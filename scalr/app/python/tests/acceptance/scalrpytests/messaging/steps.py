
from lettuce import *

import gevent

import os
import sys
import yaml
import time
import string
import random
import subprocess as subps
import multiprocessing as mp

from gevent import pywsgi

from sqlalchemy import and_
from sqlalchemy import desc
from sqlalchemy import func
from scalrpy.util import dbmanager

from scalrpytests.steplib import lib


BASE_DIR = os.path.dirname(os.path.abspath(__file__))
ETC_DIR = os.path.abspath(BASE_DIR + '/../../../etc')


@step(u"I have test config")
def test_config(step):
    try:
        world.config = yaml.safe_load(
                open(ETC_DIR + '/config.yml'))['scalr']['msg_sender']
        assert True
    except Exception:
        assert False


@step(u'I wait (\d+) seconds')
def wait_sec(step, sec):
    lib.wait_sec(int(sec))
    assert True


@step(u"I drop test database")
def drop_db(step):
    assert lib.drop_db(world.config['connections']['mysql'])


@step(u"I create test database")
def create_db(step):
    assert lib.create_db(world.config['connections']['mysql'])


@step(u"I create table '(.*)' in test database")
def create_table(step, tbl_name):
    if tbl_name == 'clients':
        assert lib.create_clients_table(world.config['connections']['mysql'])
    elif tbl_name == 'farms':
        assert lib.create_farms_table(world.config['connections']['mysql'])
    elif tbl_name == 'farm_roles':
        assert lib.create_farm_roles_table(world.config['connections']['mysql'])
    elif tbl_name == 'farm_role_settings':
        assert lib.create_farm_role_settings_table(world.config['connections']['mysql'])
    elif tbl_name == 'servers':
        assert lib.create_servers_table(world.config['connections']['mysql'])
    elif tbl_name == 'server_properties':
        assert lib.create_server_properties_table(world.config['connections']['mysql'])
    elif tbl_name == 'messages':
        assert lib.create_messages_table(world.config['connections']['mysql'])
    elif tbl_name == 'farm_settings':
        assert lib.create_farm_settings_table(world.config['connections']['mysql'])
    elif tbl_name == 'role_behaviors':
        assert lib.create_role_behaviors_table(world.config['connections']['mysql'])
    else:
        assert False


@step(u"I have (\d+) messages with status (\d+) and type '(.*)'")
def fill_tables1(step, count, st, tp):
    try:
        world.msgs_id = {}
        world.srvs_id = []

        db_manager = dbmanager.DBManager(world.config['connections']['mysql'])
        db = db_manager.get_db()

        for i in range(int(count)):
            while True:
                msg_id = ''.join(random.choice(string.ascii_uppercase +\
                        string.digits) for x in range(75))
                if db.messages.filter(
                        db.messages.messageid==msg_id).first() is None:
                    break
                continue

            while True:
                farm_id = random.randint(1, 9999)
                if db.farm_settings.filter(
                        db.farm_settings.farmid==farm_id).first() is None:
                    break
                continue

            while True:
                srv_id = ''.join(random.choice(string.ascii_uppercase +\
                        string.digits) for x in range(36))
                if db.servers.filter(
                        db.servers.server_id==srv_id).first() is None:
                    break
                continue

            db.messages.insert(
                    messageid=msg_id, status=int(st), handle_attempts=0,
                    dtlasthandleattempt=func.now(), message='some text here',
                    server_id=srv_id, type='%s' %tp, message_version=2)
            world.msgs_id.setdefault(msg_id, {}).setdefault('status', st)

            db.servers.insert(
                    farm_id=farm_id, server_id=srv_id, env_id=1, status='Running',
                    remote_ip='127.0.0.1')

            world.srvs_id.append(srv_id)

        db.commit()
        for srv_id in world.srvs_id:
            db.server_properties.insert(
                    server_id=srv_id, name='scalarizr.key', value='hoho')
            db.server_properties.insert(
                    server_id=srv_id, name='scalarizr.ctrl_port', value=None)
        db.commit()
        db.session.close()
        assert True

    except Exception:
        assert False
  

@step(u"I have (\d+) vpc messages with status (\d+) and type '(.*)'")
def fill_tables2(step, count, st, tp):
    try:
        world.msgs_id = {}
        world.srvs_id = []

        db_manager = dbmanager.DBManager(world.config['connections']['mysql'])
        db = db_manager.get_db()

        for i in range(int(count)):
            while True:
                msg_id = ''.join(random.choice(string.ascii_uppercase +\
                        string.digits) for x in range(75))
                if db.messages.filter(
                        db.messages.messageid==msg_id).first() is None:
                    break
                continue
            while True:
                msg_id_router = ''.join(random.choice(string.ascii_uppercase +\
                        string.digits) for x in range(75))
                if db.messages.filter(
                        db.messages.messageid==msg_id_router).first() is None:
                    break
                continue
            while True:
                farm_id = random.randint(1, 20000)
                if db.farm_settings.filter(
                        db.farm_settings.farmid==farm_id).first() is None:
                    break
                continue

            while True:
                farm_role_id = random.randint(1, 20000)
                if db.role_behaviors.filter(
                        db.role_behaviors.role_id==farm_role_id).first() is None:
                    break
                continue
            while True:
                farm_role_id_router = random.randint(1, 20000)
                if db.role_behaviors.filter(
                        db.role_behaviors.role_id==farm_role_id_router).first() is None:
                    break
                continue
            while True:
                srv_id = ''.join(random.choice(string.ascii_uppercase +\
                        string.digits) for x in range(36))
                if db.servers.filter(
                        db.servers.server_id==srv_id).first() is None:
                    break
                continue
            while True:
                srv_id_router = ''.join(random.choice(string.ascii_uppercase +\
                        string.digits) for x in range(36))
                if db.servers.filter(
                        db.servers.server_id==srv_id_router).first() is None:
                    break
                continue

            db.messages.insert(
                    messageid=msg_id, status=int(st), handle_attempts=0,
                    dtlasthandleattempt=func.now(), message='some text here',
                    server_id=srv_id, type='%s' %tp, message_version=2)
            db.messages.insert(
                    messageid=msg_id_router, status=int(st), handle_attempts=0,
                    dtlasthandleattempt=func.now(), message='some text here',
                    server_id=srv_id_router, type='%s' %tp, message_version=2)

            db.farms.insert(
                    farm_id=farm_id, env_id=1)

            db.farm_roles.insert(
                    farmid=farm_id, role_id=farm_role_id)
            db.farm_roles.insert(
                    farmid=farm_id, role_id=farm_role_id_router)

            db.servers.insert(
                    farm_id=farm_id, farm_roleid=farm_role_id, server_id=srv_id, env_id=1, status='Running',
                    local_ip='244.244.244.244')
            db.servers.insert(
                    farm_id=farm_id, farm_roleid=farm_role_id_router, server_id=srv_id_router, env_id=1, status='Running',
                    local_ip='254.254.254.254')

            db.role_behaviors.insert(role_id=farm_role_id, behavior='not router')
            db.role_behaviors.insert(role_id=farm_role_id_router, behavior='router')

            db.farm_settings.insert(
                    farmid=farm_id, name='ec2.vpc.id', value='1')

            id_ = db.farm_roles.filter(db.farm_roles.role_id==farm_role_id_router, db.farm_roles.farmid==farm_id).first().id
            db.farm_role_settings.insert(
                    farm_roleid=id_, name='router.vpc.ip', value='127.0.0.1')

            world.srvs_id.append(srv_id)
            world.srvs_id.append(srv_id_router)

        db.commit()
        for srv_id in world.srvs_id:
            db.server_properties.insert(
                    server_id=srv_id, name='scalarizr.key', value='hoho')
            db.server_properties.insert(
                    server_id=srv_id, name='scalarizr.ctrl_port', value='8013')

        db.commit()
        db.session.close()
        assert True

    except Exception:
        print sys.exc_info()
        assert False

def answer(environ, start_response):
    gevent.sleep(1)
    start_response('201 OK', [('Content-Type', 'text/html')])
    yield '<b>Hello world!</b>\n'


@step(u"I start wsgi server")
def start_wsgi_server(step):
    world.server_proc = mp.Process(target=pywsgi.WSGIServer(('127.0.0.1', 8013), answer).serve_forever)
    world.vpc_proc = mp.Process(target=pywsgi.WSGIServer(('127.0.0.1', 80), answer).serve_forever)
    world.server_proc.start()
    try:
        subps.call(['service', 'apache2', 'stop'])
        assert True
    except Exception:
        try:
            subps.call(['killall', '-9', 'apache2'])
            assert True
        except Exception:
            assert False
    world.vpc_proc.start()


@step(u"I stop wsgi server")
def stop_wsgi_server(step):
    world.server_proc.terminate()
    world.vpc_proc.terminate()
    try:
        subps.call(['service', 'apache2', 'start'])
        assert True
    except Exception:
        try:
            subps.call(['apache2'])
            assert True
        except Exception:
            assert False


@step(u"I make prepare")
def prepare(step):
    step.given("I have test config")
    step.given("I stop all mysql services")
    step.given("I start mysql service")
    step.given("I drop test database")
    step.given("I create test database")
    step.given("I create table 'messages' in test database")
    step.given("I create table 'farms' in test database")
    step.given("I create table 'farm_role_settings' in test database")
    step.given("I create table 'servers' in test database")
    step.given("I create table 'farm_roles' in test database")
    step.given("I create table 'server_properties' in test database")
    step.given("I create table 'farm_settings' in test database")
    step.given("I create table 'role_behaviors' in test database")


@step(u"I stop all mysql services")
def stop_all_mysql_services(step):
    try:
        subps.call(['service', 'mysql', 'stop'])
        assert True
    except Exception:
        try:
            subps.call(['killall', '-9', 'mysqld'])
            assert True
        except Exception:
            assert False


@step(u"I start mysql service")
def start_mysql_service(step):
    try:
        subps.call(['service', 'mysql', 'start'])
        assert True
    except Exception:
        try:
            subps.call(['mysqld'])
            assert True
        except Exception:
            assert False


@step(u"I start messaging daemon")
def start_daemon(step):
    try:
        QSIZE = 1024
        CRATIO = 120 # Common ratio for send interval progression
        db_manager = dbmanager.DBManager(world.config['connections']['mysql'])
        db = db_manager.get_db()
        where1 = and_(db.messages.type=='out')
        where2 = and_(db.messages.message_version==2)
        where3 = and_(func.unix_timestamp(db.messages.dtlasthandleattempt) +\
                db.messages.handle_attempts * CRATIO < func.unix_timestamp(
                func.now()))
        msgs = db.messages.filter(db.messages.status==0,\
                where1, where2, where3).order_by(
                desc(db.messages.id)).all()[0:QSIZE]

        world.right_msgs = [msg.messageid for msg in msgs]

        assert len(world.right_msgs) != 0

        cnf = ETC_DIR + '/config.yml'
        subps.Popen(['python', '-m', 'scalrpy.messaging', '--start', '-vvv', '-c', cnf])
        time.sleep(2)

        ps = subps.Popen(['ps -ef'], shell=True, stdout=subps.PIPE)
        output = ps.stdout.read()
        ps.stdout.close()
        ps.wait()
        assert 'scalrpy.messaging --start' in output
    except Exception:
        assert False
    finally:
        db.session.close()
        db.session.remove()


@step(u"I stop messaging daemon")
def stop_daemon(step):
    cnf = ETC_DIR + '/config.yml'
    subps.Popen(['python', '-m', 'scalrpy.messaging', '--stop', '-vvv', '-c', cnf])
    time.sleep(2)

    ps = subps.Popen(['ps -ef'], shell=True, stdout=subps.PIPE)
    output = ps.stdout.read()
    ps.stdout.close()
    ps.wait()
    assert 'scalrpy.messaging --start' not in output


@step(u"I see right messages were delivered")
def right_messages_were_delivered(step):
    db_manager = dbmanager.DBManager(world.config['connections']['mysql'])
    db = db_manager.get_db()

    msgs = db.messages.filter(db.messages.messageid.in_(world.right_msgs)).all()

    assert len(msgs) != 0

    for msg in msgs:
        assert msg.status == 1



@step(u"I see right messages have (\d+) handle_attempts")
def right_messages_have_right_handle_attemps(step, val):
    db_manager = dbmanager.DBManager(world.config['connections']['mysql'])
    db = db_manager.get_db()

    msgs = db.messages.filter(db.messages.messageid.in_(world.right_msgs)).all()
    for msg in msgs:
        assert msg.handle_attempts == int(val)

