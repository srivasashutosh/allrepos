
import os
import sys
import time
import yaml
import netsnmp
import logging
import rrdtool
import argparse
import multiprocessing as mp

from multiprocessing import pool

from scalrpy.util import helper
from scalrpy.util import dbmanager
from scalrpy.util import basedaemon

from sqlalchemy import and_
from sqlalchemy import exc as db_exc

from scalrpy.util.scalarizr_api.binding.jsonrpc_http import HttpServiceProxy

import scalrpy

oids_data = {
        'cpu':{
                'user':'.1.3.6.1.4.1.2021.11.50.0',
                'nice':'.1.3.6.1.4.1.2021.11.51.0',
                'system':'.1.3.6.1.4.1.2021.11.52.0',
                'idle':'.1.3.6.1.4.1.2021.11.53.0',
        },
        'la':{
                'la1':'.1.3.6.1.4.1.2021.10.1.3.1',
                'la5':'.1.3.6.1.4.1.2021.10.1.3.2',
                'la15':'.1.3.6.1.4.1.2021.10.1.3.3',
        },
        'mem':{
                'swap':'.1.3.6.1.4.1.2021.4.3.0',
                'swapavail':'.1.3.6.1.4.1.2021.4.4.0',
                'total':'.1.3.6.1.4.1.2021.4.5.0',
                'avail':'.1.3.6.1.4.1.2021.4.6.0',
                'free':'.1.3.6.1.4.1.2021.4.11.0',
                'shared':'.1.3.6.1.4.1.2021.4.13.0',
                'buffer':'.1.3.6.1.4.1.2021.4.14.0',
                'cached':'.1.3.6.1.4.1.2021.4.15.0',
        },
        'net':{
                'in':'.1.3.6.1.2.1.2.2.1.10.2',
                'out':'.1.3.6.1.2.1.2.2.1.16.2',
        }
}

cpu_source = [
        'DS:user:COUNTER:600:U:U',
        'DS:system:COUNTER:600:U:U',
        'DS:nice:COUNTER:600:U:U',
        'DS:idle:COUNTER:600:U:U'
]

cpu_archive = [
        'RRA:AVERAGE:0.5:1:800',
        'RRA:AVERAGE:0.5:6:800',
        'RRA:AVERAGE:0.5:24:800',
        'RRA:AVERAGE:0.5:288:800',
        'RRA:MAX:0.5:1:800',
        'RRA:MAX:0.5:6:800',
        'RRA:MAX:0.5:24:800',
        'RRA:MAX:0.5:288:800',
        'RRA:LAST:0.5:1:800',
        'RRA:LAST:0.5:6:800',
        'RRA:LAST:0.5:24:800',
        'RRA:LAST:0.5:288:800']

la_source = [
        'DS:la1:GAUGE:600:U:U',
        'DS:la5:GAUGE:600:U:U',
        'DS:la15:GAUGE:600:U:U'
]

la_archive = [
        'RRA:AVERAGE:0.5:1:800',
        'RRA:AVERAGE:0.5:6:800',
        'RRA:AVERAGE:0.5:24:800',
        'RRA:AVERAGE:0.5:288:800',
        'RRA:MAX:0.5:1:800',
        'RRA:MAX:0.5:6:800',
        'RRA:MAX:0.5:24:800',
        'RRA:MAX:0.5:288:800',
        'RRA:LAST:0.5:1:800',
        'RRA:LAST:0.5:6:800',
        'RRA:LAST:0.5:24:800',
        'RRA:LAST:0.5:288:800'
]

mem_source = [
        'DS:swap:GAUGE:600:U:U',
        'DS:swapavail:GAUGE:600:U:U',
        'DS:total:GAUGE:600:U:U',
        'DS:avail:GAUGE:600:U:U',
        'DS:free:GAUGE:600:U:U',
        'DS:shared:GAUGE:600:U:U',
        'DS:buffer:GAUGE:600:U:U',
        'DS:cached:GAUGE:600:U:U'
]

mem_archive = [
        'RRA:AVERAGE:0.5:1:800',
        'RRA:AVERAGE:0.5:6:800',
        'RRA:AVERAGE:0.5:24:800',
        'RRA:AVERAGE:0.5:288:800',
        'RRA:MAX:0.5:1:800',
        'RRA:MAX:0.5:6:800',
        'RRA:MAX:0.5:24:800',
        'RRA:MAX:0.5:288:800',
        'RRA:LAST:0.5:1:800',
        'RRA:LAST:0.5:6:800',
        'RRA:LAST:0.5:24:800',
        'RRA:LAST:0.5:288:800'
]

net_source = [
        'DS:in:COUNTER:600:U:21474836480',
        'DS:out:COUNTER:600:U:21474836480'
]

net_archive = [
        'RRA:AVERAGE:0.5:1:800',
        'RRA:AVERAGE:0.5:6:800',
        'RRA:AVERAGE:0.5:24:800',
        'RRA:AVERAGE:0.5:288:800',
        'RRA:MAX:0.5:1:800',
        'RRA:MAX:0.5:6:800',
        'RRA:MAX:0.5:24:800',
        'RRA:MAX:0.5:288:800',
        'RRA:LAST:0.5:1:800',
        'RRA:LAST:0.5:6:800',
        'RRA:LAST:0.5:24:800',
        'RRA:LAST:0.5:288:800'
]

servers_num_source = [
        'DS:s_running:GAUGE:600:U:U'
]

servers_num_archive = [
        'RRA:AVERAGE:0.5:1:800',
        'RRA:AVERAGE:0.5:6:800',
        'RRA:AVERAGE:0.5:24:800',
        'RRA:AVERAGE:0.5:288:800',
        'RRA:MAX:0.5:1:800',
        'RRA:MAX:0.5:6:800',
        'RRA:MAX:0.5:24:800',
        'RRA:MAX:0.5:288:800',
        'RRA:LAST:0.5:1:800',
        'RRA:LAST:0.5:6:800',
        'RRA:LAST:0.5:24:800',
        'RRA:LAST:0.5:288:800'
]

io_source = [
        'DS:read:COUNTER:600:U:U',
        'DS:write:COUNTER:600:U:U',
        'DS:rbyte:COUNTER:600:U:U',
        'DS:wbyte:COUNTER:600:U:U'
]

io_archive = [
        'RRA:AVERAGE:0.5:1:800',
        'RRA:AVERAGE:0.5:6:800',
        'RRA:AVERAGE:0.5:24:800',
        'RRA:AVERAGE:0.5:288:800',
        'RRA:MAX:0.5:1:800',
        'RRA:MAX:0.5:6:800',
        'RRA:MAX:0.5:24:800',
        'RRA:MAX:0.5:288:800',
        'RRA:LAST:0.5:1:800',
        'RRA:LAST:0.5:6:800',
        'RRA:LAST:0.5:24:800',
        'RRA:LAST:0.5:288:800'
]


BASE_DIR = os.path.dirname(os.path.abspath(__file__))
ETC_DIR = os.path.abspath(BASE_DIR + '/../../../etc')

config = {
    'farm_procs':2,
    'serv_thrds':30,
    'rrd_thrds':2,
    'metrics':['cpu', 'la', 'mem', 'net'],
    'rrd_db_dir':'/tmp/rrd_db_dir',
    'pid_file':'/var/run/scalr.stats-poller.pid',
    'log_file':'/var/log/scalr.stats-poller.log'
}

logger = logging.getLogger(__file__)

rrd_queue = mp.Queue(1024 * 16)



def post_processing(results):

    ra = {}
    fa = {}
    ras = {}
    fas = {}
    rs = {}
    fs = {}

    for result in results:

        r_key = '%s/%s' % (result['farm_id'], result['farm_role_id'])
        f_key = '%s' % result['farm_id']
        ra.setdefault(r_key, {})
        fa.setdefault(f_key, {})
        ras.setdefault(r_key, {})
        fas.setdefault(f_key, {})
        try:
            rs[r_key]['servers']['s_running'] += 1
        except KeyError:
            rs.setdefault(r_key, {'servers':{'s_running':1}})
        try:
            fs[f_key]['servers']['s_running'] += 1
        except KeyError:
            fs.setdefault(f_key, {'servers':{'s_running':1}})

        for metric_group, metrics in result['data'].iteritems():
            ra[r_key].setdefault(metric_group, {})
            fa[f_key].setdefault(metric_group, {})
            ras[r_key].setdefault(metric_group, {})
            fas[f_key].setdefault(metric_group, {})
            for metric, value in metrics.iteritems():
                ra[r_key][metric_group].setdefault(metric, None)
                fa[f_key][metric_group].setdefault(metric, None)
                ras[r_key][metric_group].setdefault(metric, 0)
                fas[f_key][metric_group].setdefault(metric, 0)
                if value is not None:
                    ras[r_key][metric_group][metric] += 1
                    if ra[r_key][metric_group][metric] is None:
                        ra[r_key][metric_group][metric] = value
                    else:
                        k = float(ras[r_key][metric_group][metric]-1) /\
                                float(ras[r_key][metric_group][metric])
                        ra[r_key][metric_group][metric] =\
                                ra[r_key][metric_group][metric] * k + value /\
                                ras[r_key][metric_group][metric]
                    fas[f_key][metric_group][metric] += 1
                    if fa[f_key][metric_group][metric] is None:
                        fa[f_key][metric_group][metric] = value
                    else:
                        k = float(fas[f_key][metric_group][metric]-1) /\
                                    float(fas[f_key][metric_group][metric])
                        fa[f_key][metric_group][metric] = \
                                fa[f_key][metric_group][metric] * k + value /\
                                fas[f_key][metric_group][metric]

    return ra, fa, rs, fs



def farm_process(tasks):
    servs_pool = pool.ThreadPool(processes=config['serv_thrds'])

    try:
        results = servs_pool.map_async(server_thread, tasks).get()
    except Exception:
        logger.exception('Exception')
    finally:
        servs_pool.close()
        servs_pool.join()

    try:
        ra, fa, rs, fs = post_processing(results)

        global rrd_queue
        for k, v in ra.iteritems():
            rrd_queue.put({'ra':{k:v}})
        
        for k, v in fa.iteritems():
            rrd_queue.put({'fa':{k:v}})

        for k, v in rs.iteritems():
            rrd_queue.put({'rs':{k:v}})

        for k, v in fs.iteritems():
            rrd_queue.put({'fs':{k:v}})
    except Exception:
        logger.exception('Exception')



def server_thread(task):
    try:
        host = task['host']
        port = task['api_port']
        key = task['api_key']
        data = ScalarizrAPI.get({'host':host, 'port':port, 'key':key}, task['metrics'])
    except Exception as e:
        logger.debug('%s scalarizr api failed: %s, trying snmp ...' % (task['host'], e))
        try:
            host = task['host']
            port = task['snmp_port']
            community = task['community']
            data = SNMP.get({'host':host, 'port':port, 'community':community}, task['metrics'])
        except Exception:
            logger.exception('Exception')

    result = {}
    try:
        global rrd_queue
        rrd_queue.put({'server':{'%s/%s/%s'
                                 % (task['farm_id'], task['farm_role_id'], task['index']):data}})

        result = {'farm_id':task['farm_id'], 'farm_role_id':task['farm_role_id'],
                  'index':task['index'], 'data':data}
    except Exception:
        logger.exception('Exception')

    return result



class StatsPoller(basedaemon.BaseDaemon):

    def __init__(self, config):
        super(StatsPoller, self).__init__(pid_file=config['pid_file'], logger_name=__file__)
        self.db_manager = dbmanager.DBManager(config['connections']['mysql'])


    def __call__(self):
        try:
            tasks = self._get_tasks()
        except (db_exc.OperationalError, db_exc.InternalError):
            logger.error(sys.exc_info())
        except Exception:
            logger.exception('Exception')

        self._process_tasks(tasks)


    def run(self):
        while True:
            start_time = time.time()
            logger.info('Start time: %s' % time.ctime())

            p = mp.Process(target=self.__call__, args=())
            p.start()
            p.join(600)
            if p.is_alive():
                logger.error('Timeout. Terminating ...')
                helper.kill_ps(p.pid, child=True)
                p.terminate()

            logger.info('Working time: %s' % (time.time() - start_time))

            if not config['interval']:
                break

            sleep_time = start_time + config['interval'] - time.time()
            if sleep_time > 0:
                time.sleep(sleep_time)


    def _get_tasks(self):
        db_manager = dbmanager.DBManager(config['connections']['mysql'])
        db = db_manager.get_db()
        session = db.session

        clients = [cli.id for cli in session.query(db.clients.id).filter_by(status='Active').all()]
        if not clients:
            logger.degub('Nothing to do')
            return

        hashs = dict((farm.id, farm.hash) for farm in session.query(
                db.farms.id, db.farms.hash).filter(db.farms.clientid.in_(clients)))

        servers = session.query(db.servers.server_id, db.servers.farm_id, db.servers.farm_roleid,
                db.servers.index, db.servers.remote_ip).filter(and_(
                db.servers.client_id.in_(clients),
                db.servers.status=='Running',
                db.servers.remote_ip!='None')).all()

        servers_id = [srv.server_id for srv in servers]
        if not servers_id:
            logger.debug('Nothing to do')
            return

        logger.debug('Number of servers: %s' % len(servers_id))

        where_port = and_(
                db.server_properties.server_id.in_(servers_id),
                db.server_properties.name=='scalarizr.snmp_port')
        snmp_ports = dict((prop.server_id, prop.value)
                for prop in session.query(db.server_properties.server_id,
                        db.server_properties.value).filter(where_port).all())
        where_port = and_(
                db.server_properties.server_id.in_(servers_id),
                db.server_properties.name=='scalarizr.api_port')
        api_ports = dict((prop.server_id, prop.value)
                for prop in session.query(db.server_properties.server_id,
                        db.server_properties.value).filter(where_port).all())
        where_key = and_(
                db.server_properties.server_id.in_(servers_id),
                db.server_properties.name=='scalarizr.key')
        api_keys = dict((prop.server_id, prop.value)
                for prop in session.query(db.server_properties.server_id,
                        db.server_properties.value).filter(where_key).all())

        session.close()
        session.remove()

        farm_tasks = {}

        for srv in servers:
            task = {
                    'farm_id':srv.farm_id, 'farm_role_id':srv.farm_roleid, 'index':srv.index,
                    'host':srv.remote_ip, 'community':hashs[srv.farm_id], 'metrics':config['metrics']}
            try:
                snmp_port = snmp_ports[srv.server_id]
            except Exception:
                snmp_port = 161
            task.update({'snmp_port':snmp_port})
            try:
                api_key = api_keys[srv.server_id]
                task.update({'api_key':api_key})
            except Exception:
                logger.debug('Scalarizr api key not found')
            try:
                api_port = api_ports[srv.server_id]
            except Exception:
                api_port = 8010
            task.update({'api_port':api_port})

            farm_tasks.setdefault(srv.farm_id, []).append(task)

        return farm_tasks


    def _process_tasks(self, farm_tasks):
        farms_pool = mp.Pool(processes=config['farm_procs'])

        chunks = [[]]
        for k, v in farm_tasks.iteritems():
            if len(chunks[-1]) < config['serv_thrds']:
                chunks[-1] += v
            else:
                chunks.append(v)

        try:
            farms_pool_result = farms_pool.map_async(farm_process, chunks)
        except Exception:
            logger.exception('Exception')
        finally:
            farms_pool.close()

        try:
            rrd_pool = pool.ThreadPool(processes=config['rrd_thrds'])

            while not farms_pool_result.ready() or not rrd_queue.empty():
                try:
                    task = rrd_queue.get(block=False)
                    rrd_pool.map_async(RRDWorker().work, [task])
                except Exception:
                    time.sleep(3)

            farms_pool.join()

            while not rrd_queue.empty():
                try:
                    task = rrd_queue.get(block=False)
                    rrd_pool.map_async(RRDWorker().work, [task])
                except Exception:
                    time.sleep(3)
        except Exception:
            logger.exception('Exception')
        finally:
            rrd_pool.close()
            rrd_pool.join()



class SNMP(object):

    @staticmethod
    def get(connection_info, metrics):
        """
        :type connection_info: dictionary 
        :param connection_info: {'host':host, 'port':port, 'community':community}

        :type metrics: list of strings
        :param metrics: list ['cpu', 'mem', 'la', 'net', 'io']

        :rtype: dictionary
        :returns: { 
                'cpu':{
                        'user':value,
                        'nice':value,
                        ...}
                'la':{
                        'la1':value,
                        ...}
                'mem':{
                        'swap':value,
                        ...}
                'net':{
                        'in':value,
                        'out':value}}
        """

        host = connection_info['host']
        port = connection_info['port']
        community = connection_info['community']

        oids = []
        for k, v in oids_data.iteritems():
            if k in metrics:
                for kk, vv in v.iteritems():
                    oids.append(vv)

        session = netsnmp.Session(
                DestHost = '%s:%s' %(host, port),
                Version = 1,
                Community = community,
                Timeout=1000000)
        Vars = netsnmp.VarList(*oids)
        
        snmp_data = dict((oid, val) for oid, val in zip(oids, session.get(Vars)))

        data = {}
        for metric_name in metrics:
            if metric_name not in oids_data:
                continue
            for metric in oids_data[metric_name].keys():
                try:
                    value = float(snmp_data[oids_data[metric_name][metric]])
                except Exception:
                    value = None
                data.setdefault(metric_name, {}).setdefault(metric, value)

        return data



class ScalarizrAPI(object):

    @staticmethod
    def _get_cpu_stat(hsp):
        cpu = hsp.sysinfo.cpu_stat(timeout=1)

        for k, v in cpu.iteritems():
            cpu[k] = float(v)

        return {'cpu':cpu}


    @staticmethod
    def _get_la_stat(hsp):
        la = hsp.sysinfo.load_average(timeout=1)

        return {'la':{'la1':float(la[0]), 'la5':float(la[1]), 'la15':float(la[2])}}


    @staticmethod
    def _get_mem_info(hsp):
        mem = hsp.sysinfo.mem_info(timeout=1)

        return {'mem':{
                    'swap':float(mem['total_swap']),
                    'swapavail':float(mem['avail_swap']),
                    'total':float(mem['total_real']),
                    'avail':0.0, # FIXME
                    'free':mem['total_free'],
                    'shared':mem['shared'],
                    'buffer':mem['buffer'],
                    'cached':mem['cached']}}


    @staticmethod
    def _get_net_stat(hsp):
        net = hsp.sysinfo.net_stats(timeout=1)

        return {'net':{
                    'in':float(net['eth0']['receive']['bytes']),
                    'out':float(net['eth0']['transmit']['bytes'])}}


    '''
    @staticmethod
    def _get_io_stat(hsp):
        io = hsp.sysinfo.disk_stats(timeout=1)
        io = dict((str(dev), {'read':io[dev]['read']['num'], 'write':io[dev]['write']['num'],
                'rbyte':io[dev]['read']['bytes'], 'wbyte':io[dev]['write']['bytes']}) for dev in io)

        return {'io':io}
    '''


    @staticmethod
    def get(connection_info, metrics):
        """
        :type connection_info: dictionary 
        :param connection_info: {'host':host, 'port':port, 'key':key}

        :type metrics: list of strings
        :param metrics: list ['cpu', 'mem', 'la', 'net', 'io']

        :rtype: dictionary
        :returns: { 
                'cpu':{
                        'user':value,
                        'nice':value,
                        ...}
                'la':{
                        'la1':value,
                        ...}
                'mem':{
                        'swap':value,
                        ...}
                'net':{
                        'in':value,
                        'out':value}}
        """

        host = connection_info['host']
        port = connection_info['port']
        key = connection_info['key']

        data = {}
        hsp = HttpServiceProxy('http://%s:%s' %(host, port), key)

        if 'cpu' in metrics:
            data.update(ScalarizrAPI._get_cpu_stat(hsp))
        if 'la' in metrics:
            data.update(ScalarizrAPI._get_la_stat(hsp))
        if 'mem' in metrics:
            data.update(ScalarizrAPI._get_mem_info(hsp))
        if 'net' in metrics:
            data.update(ScalarizrAPI._get_net_stat(hsp))
        if 'io' in metrics:
            data.update(ScalarizrAPI._get_io_stat(hsp))

        return data



class RRDWriter():

    def __init__(self, source, archive):
        self.source = source
        self.archive = archive


    def _create_db(self, rrd_db_path):
        if not os.path.exists(os.path.dirname(rrd_db_path)):
            os.makedirs(os.path.dirname(rrd_db_path))
        rrdtool.create(rrd_db_path, self.source, self.archive)


    def write(self, rrd_db_path, data):
        """
        type rrd_db_path: string
        param rrd_db_path: path to rrd database directory

        type data: dictionary
        param data: dictionary {metric name:value} with data to write
        """

        rrd_db_path = str(rrd_db_path)

        if not os.path.isfile(rrd_db_path):
            self._create_db(rrd_db_path)

        data_to_write = 'N'
        for s in self.source:
            data_type = {'COUNTER':int, 'GAUGE':float}[s.split(':')[2]]
            try:
                data_to_write += ':%s' % (data_type)(data[s.split(':')[1]])
            except Exception:
                data_to_write += ':None'

        try:
            logger.debug('%s, %s, %s' %(time.time(), rrd_db_path, data_to_write))
            rrdtool.update(rrd_db_path, "--daemon", "unix:/var/run/rrdcached.sock", data_to_write)
        except rrdtool.error, e:
            logger.error('RRDTool update error:%s, %s' %(e, rrd_db_path))
        except Exception:
            logger.exception('Exception')



class RRDWorker(object):

    writers = {
            'cpu':RRDWriter(cpu_source, cpu_archive),
            'la':RRDWriter(la_source, la_archive),
            'mem':RRDWriter(mem_source, mem_archive),
            'net':RRDWriter(net_source, net_archive),
            'servers':RRDWriter(servers_num_source, servers_num_archive),
            'io':RRDWriter(io_source, io_archive)}


    def _x1x2(self, farm_id):
        i = int(farm_id[-1])-1
        x1 = str(i-5*(i/5)+1)[-1]
        x2 = str(i-5*(i/5)+6)[-1]

        return 'x%sx%s' % (x1, x2)


    def _process_server_task(self, task):
        for key, data in task.iteritems():
            farm_id, farm_role_id, index = key.split('/')

            for metrics_group_name, metrics_group in data.iteritems():
                RRDWorker.writers[metrics_group_name].write(
                        '%s/%s/%s/INSTANCE_%s_%s/%sSNMP/db.rrd'\
                        %(config['rrd_db_dir'], self._x1x2(farm_id), farm_id, farm_role_id,
                        index, metrics_group_name.upper()), metrics_group)


    def _process_ra_task(self, task):
        for key, data in task.iteritems():
            farm_id, farm_role_id = key.split('/')

            for metrics_group_name, metrics_group in data.iteritems():
                RRDWorker.writers[metrics_group_name].write(
                        '%s/%s/%s/FR_%s/%sSNMP/db.rrd'\
                        %(config['rrd_db_dir'], self._x1x2(farm_id), farm_id, farm_role_id,
                        metrics_group_name.upper()), metrics_group)


    def _process_fa_task(self, task):
        for key, data in task.iteritems():
            farm_id = key

            for metrics_group_name, metrics_group in data.iteritems():
                RRDWorker.writers[metrics_group_name].write(
                        '%s/%s/%s/FARM/%sSNMP/db.rrd'\
                        %(config['rrd_db_dir'], self._x1x2(farm_id), farm_id,
                        metrics_group_name.upper()), metrics_group)


    def _process_rs_task(self, task):
        for key, data in task.iteritems():
            farm_id, farm_role_id = key.split('/')

            for metrics_group_name, metrics_group in data.iteritems():
                RRDWorker.writers[metrics_group_name].write(
                        '%s/%s/%s/FR_%s/SERVERS/db.rrd'\
                        %(config['rrd_db_dir'], self._x1x2(farm_id), farm_id, farm_role_id),
                        metrics_group)


    def _process_fs_task(self, task):
        for key, data in task.iteritems():
            farm_id = key

            for metrics_group_name, metrics_group in data.iteritems():
                RRDWorker.writers[metrics_group_name].write(
                        '%s/%s/%s/FARM/SERVERS/db.rrd'\
                        %(config['rrd_db_dir'], self._x1x2(farm_id), farm_id), metrics_group)


    def work(self, task):
        task_name = task.keys()[0]
        if task_name == 'server':
            self._process_server_task(task[task_name])
        elif task_name == 'ra':
            self._process_ra_task(task[task_name])
        elif task_name == 'fa':
            self._process_fa_task(task[task_name])
        elif task_name == 'rs':
            self._process_rs_task(task[task_name])
        elif task_name == 'fs':
            self._process_fs_task(task[task_name])



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

        parser.add_argument('-i', '--interval', type=int, default=0,
                help="execution interval in seconds. Default is 0 - exec once")
        parser.add_argument('-p','--pid_file', default=None, help="Pid file")
        parser.add_argument('-m', '--metrics', default=None, choices=['cpu', 'la', 'mem', 'net'],
                action='append', help="metrics type for processing")
        parser.add_argument('-c', '--config_file', default='%s/config.yml' % ETC_DIR,
                            help='config file')
        parser.add_argument('-v', '--verbosity', default=1, action='count',
                help='increase output verbosity [0:4]. Default is 1 - Error')
        parser.add_argument('--version', action='version', version='Version %s'
                            % scalrpy.__version__)

        args = parser.parse_args()

        try:
            cnf = yaml.safe_load(open(args.config_file))['scalr']['stats_poller']
        except IOError as e:
            sys.stderr.write('%s\n' % str(e))
            sys.exit(1)
        except KeyError:
            sys.stderr.write('%s\nYou must define \'stats_poller\' section in config file\n'
                             % str(sys.exc_info()))
            sys.exit(1)

        configure(args, cnf)

    except Exception:
        sys.stderr.write('%s\n' % str(sys.exc_info()))
        sys.exit(1)

    try:
        daemon = StatsPoller(config)

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

        logger.info('Exit')

    except KeyboardInterrupt:
        logger.critical(sys.exc_info()[0])
        sys.exit(0)
    except SystemExit:
        pass
    except Exception:
        logger.critical('Something happened and I think I died')
        logger.exception('Critical exception')
        sys.exit(1)


if __name__ == '__main__':
    main()

