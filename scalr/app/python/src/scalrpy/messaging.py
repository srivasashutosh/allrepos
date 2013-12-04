
from gevent import monkey
monkey.patch_all()

import socket
socket.setdefaulttimeout(5)

import os
import sys
import time
import yaml
import logging
import urllib2
import binascii
import argparse
import gevent.pool

from scalrpy.util import helper
from scalrpy.util import dbmanager
from scalrpy.util import basedaemon
from scalrpy.util import cryptotool

from sqlalchemy import and_
from sqlalchemy import asc 
from sqlalchemy import func
from sqlalchemy import exc as db_exc

import scalrpy

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
ETC_DIR = os.path.abspath(BASE_DIR + '/../../../etc')

config = {
    'qsize':1024,
    'cratio':120,
    'pool_size':10,
    'inst_conn_policy':'public',
    'pid_file':'/var/run/scalr.messaging.pid',
    'log_file':'/var/log/scalr.messaging.log'
}

logger = logging.getLogger(__file__)


class Messaging(basedaemon.BaseDaemon):

    def __init__(self, config):
        super(Messaging, self).__init__(pid_file=config['pid_file'], logger_name=__file__)
        self.db_manager = dbmanager.DBManager(config['connections']['mysql'])


    def _server_is_active(self, srv):
        return srv.status in (
                'Running', 'Initializing', 'Importing', 'Temporary', 'Pending terminate')


    def _encrypt(self, server_id, crypto_key, data, headers=None):
        crypto_algo = dict(name="des_ede3_cbc", key_size=24, iv_size=8)
        data = cryptotool.encrypt(crypto_algo, data, binascii.a2b_base64(crypto_key))
        headers = headers or {}

        headers['X-Signature'], headers['Date'] = cryptotool.sign_http_request(data, crypto_key)
        headers['X-Server-Id'] = server_id

        return data, headers


    def _send(self, task):
        msg = task['msg']
        req = task['req']

        db = self.db_manager.get_db()
        db.session.add(msg)

        try:
            logger.debug('Send message msg_id:%s %s %s'
                         % (msg.messageid, req.get_host(), req.header_items()))
            code = urllib2.urlopen(req, timeout=5).getcode()
            if code != 201:
                raise Exception(code, 'Delivery failed')
            logger.debug('Delivery ok msg_id:%s %s' % (msg.messageid, req.get_host()))
            msg.status = 1
            msg.message = ''
            msg.dtlasthandleattempt = func.now()
            if msg.message_name == 'ExecScript':
                db.delete(msg)
        except Exception as e:
            if type(e) in (urllib2.URLError, socket.timeout) and\
                    ('Connection refused' in str(e) or 'timed out' in str(e)):
                logger.warning('Delivery failed msg_id:%s %s error:%s' % (msg.messageid, req.get_host(), e))
            else:
                logger.error('Delivery failed msg_id:%s %s error:%s' % (msg.messageid, req.get_host(), e))
            msg.handle_attempts += 1
            msg.status = 0 if msg.handle_attempts < 3 else 3
            msg.dtlasthandleattempt = func.now()
        finally:
            while True:
                try:
                    db.commit()
                    db.session.close()
                    break
                except (db_exc.OperationalError, db_exc.InternalError):
                    logger.error(sys.exc_info())
                    time.sleep(10)


    def run(self):
        db = self.db_manager.get_db()

        while True:
            try:
                db.servers
                db.farm_roles
                db.farm_settings
                db.role_behaviors
                db.server_properties
                db.farm_role_settings
                break
            except (db_exc.OperationalError, db_exc.InternalError):
                logger.error(sys.exc_info())
                time.sleep(10)

        timestep = 5
        wrk_pool = gevent.pool.Pool(config['pool_size'])

        while True:
            try:
                where1 = and_(
                        db.messages.type=='out',
                        db.messages.status==0,
                        db.messages.message_version==2)

                where2 = and_(
                        func.unix_timestamp(db.messages.dtlasthandleattempt) +\
                        db.messages.handle_attempts *\
                        config['cratio'] < func.unix_timestamp(func.now()))

                msgs = dict((msg.messageid, msg) for msg in\
                        db.messages.filter(where1, where2).order_by(
                        asc(db.messages.id)).all()[0:config['qsize']])

                if not msgs:
                    time.sleep(timestep)
                    continue

                srvs_id = [msg.server_id for msg in msgs.values()]
                srvs = dict((srv.server_id, srv) for srv in\
                        db.servers.filter(db.servers.server_id.in_(srvs_id)).all())

                where = and_(
                        db.server_properties.server_id.in_(srvs_id),
                        db.server_properties.name=='scalarizr.key')
                keys_query = db.server_properties.filter(where).all()
                keys = dict((el.server_id, el.value) for el in keys_query)

                where = and_(
                        db.server_properties.server_id.in_(srvs_id),
                        db.server_properties.name=='scalarizr.ctrl_port')
                ports_query = db.server_properties.filter(where).all()
                ports = dict((el.server_id, el.value if el and el.value else 8013)
                             for el in ports_query)

                tasks = []
                for msg in msgs.values():
                    try:
                        srv = srvs[msg.server_id]
                    except KeyError:
                        logging.warning('Server with server_id %s dosn\'t exist. Delete message %s'
                                        %(msg.server_id, msg.messageid))
                        db.delete(msg)
                        continue

                    if not self._server_is_active(srv):
                        continue

                    ip = {'public':srv.remote_ip, 'local':srv.local_ip, 'auto':srv.remote_ip
                            if srv.remote_ip else srv.local_ip}[config['inst_conn_policy']]

                    try:
                        key = keys[msg.server_id]
                    except KeyError: 
                        logging.error('Server %s has not scalarizr key' % msg.server_id)
                        continue

                    try:
                        port = ports[msg.server_id]
                    except KeyError:
                        port = 8013

                    req_host = '%s:%s' % (ip, port)
                    data, headers = self._encrypt(msg.server_id, key, msg.message)

                    where = and_(
                            db.farm_settings.farmid==srv.farm_id,
                            db.farm_settings.name=='ec2.vpc.id')
                    is_vpc = db.farm_settings.filter(where).first()
                    
                    if(is_vpc):
                        where = and_(
                                db.role_behaviors.behavior=='router')
                        vpc_roles = [behavior.role_id for behavior
                                     in db.role_behaviors.filter(where).all()]

                        where = and_(
                                db.farm_roles.role_id.in_(vpc_roles), db.farm_roles.farmid==srv.farm_id)
                        db_farm_role = db.farm_roles.filter(where).first()

                        if db_farm_role:
                            logger.debug('Message:%s for VPC server:%s' % (msg.messageid, srv.server_id))
                            if srv.remote_ip:
                                ip = srv.remote_ip
                                req_host = '%s:%s' % (srv.remote_ip, port)
                            else:
                                where = and_(
                                        db.farm_role_settings.farm_roleid==db_farm_role.id,
                                        db.farm_role_settings.name=='router.vpc.ip')
                                ip_query = db.farm_role_settings.filter(where).first()
                                if ip_query and ip_query.value:
                                    ip = ip_query.value
                                    req_host = '%s:80' % ip
                                    headers['X-Receiver-Host'] = srv.local_ip
                                    headers['X-Receiver-Port'] = port
                                else:
                                    ip = None

                    if ip == None or ip == 'None':
                        logger.warning('Server: %s Null ip, delete message %s'
                                       % (srv.server_id, msg.messageid))
                        db.delete(msg)
                        continue
                    
                    url = 'http://%s/%s' % (req_host, 'control')
                    req = urllib2.Request(url, data, headers)

                    db.session.expunge(msg)
                    tasks.append({'msg':msg, 'req':req})

                wrk_pool.map_async(self._send, tasks)
                gevent.sleep(0)
                wrk_pool.join()

            except (db_exc.OperationalError, db_exc.InternalError):
                logger.error(sys.exc_info())
                time.sleep(10)
            except Exception:
                logger.exception('Exception')
            finally:
                while True:
                    try:
                        db.commit()
                        db.session.close()
                        break
                    except (db_exc.OperationalError, db_exc.InternalError):
                        logger.error(sys.exc_info())
                        time.sleep(10)

            time.sleep(timestep)


def configure(args, cnf):
    global config

    for k, v in cnf.iteritems():
            config.update({k:v})

    for k, v in vars(args).iteritems():
        if v is not None:
            config.update({k:v})

    helper.configure_log(__file__, log_level=config['verbosity'], log_file=config['log_file'],
                         log_size=1024*500)


def main():
    try:
        parser = argparse.ArgumentParser()

        group = parser.add_mutually_exclusive_group()

        group.add_argument('--start', action='store_true', default=False, help='start daemon')
        group.add_argument('--stop', action='store_true', default=False, help='stop daemon')
        group.add_argument('--restart', action='store_true', default=False, help='restart daemon')

        parser.add_argument('-p', '--pid_file', default=None, help="Pid file")
        parser.add_argument('-c', '--config_file', default='%s/config.yml' % ETC_DIR,
                            help='config file')
        parser.add_argument('-v', '--verbosity', action='count', default=1,
                            help='increase output verbosity [0:4]. Default is 1 - ERROR')
        parser.add_argument('--version', action='version', version='Version %s'
                            % scalrpy.__version__)
        
        args = parser.parse_args()

        try:
            cnf = yaml.safe_load(open(args.config_file))['scalr']['msg_sender']
        except IOError as e:
            sys.stderr.write('%s\n' % str(e))
            sys.exit(1)
        except KeyError:
            sys.stderr.write('%s\nYou must define \'msg_sender\' section in config file\n'
                             % str(sys.exc_info()))
            sys.exit(1)

        configure(args, cnf)

    except Exception:
        sys.stderr.write('%s\n' % str(sys.exc_info()))
        sys.exit(1)

    try:
        daemon = Messaging(config)

        if args.start:
            if not helper.check_pid(config['pid_file']):
                logger.critical('Another copy of process already running. Exit') 
                sys.exit(0)
            daemon.start()
        elif args.stop:
            daemon.stop()
        elif args.restart:
            daemon.restart()
        else:
            print 'Usage %s -h' % sys.argv[0]

    except SystemExit:
        pass
    except Exception:
        logger.critical('Something happened and I think I died')
        logger.exception('Critical exception')
        sys.exit(1)


if __name__ == '__main__':
    main()

