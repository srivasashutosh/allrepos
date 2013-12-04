
import mock
import unittest

from scalrpy import stats_poller



def patch_snmp():
    snmp = stats_poller.SNMP()
    snmp.get_data = mock.Mock(return_value = {
            'cpu':{
                    'user':1,
                    'nice':1,
                    'system':1,
                    'idle':1},
            'la':{
                    'la1':1.0,
                    'la5':1.0,
                    'la15':1.0},
            'mem':{
                    'swap':1,
                    'swapavail':1,
                    'total':1,
                    'avail':1,
                    'free':1,
                    'shared':1,
                    'buffer':1,
                    'cached':1},
            'net':{
                    'in':1,
                    'out':1}})
    return snmp



def patch_scalarizr_api():
    api = stats_poller.ScalarizrAPI()
    api.get_data = mock.Mock(return_value = {
            'cpu':{
                    'user':1,
                    'nice':1,
                    'system':1,
                    'idle':1},
            'la':{
                    'la1':1.0,
                    'la5':1.0,
                    'la15':1.0},
            'mem':{
                    'swap':1,
                    'swapavail':1,
                    'total':1,
                    'avail':1,
                    'free':1,
                    'shared':1,
                    'buffer':1,
                    'cached':1},
            'net':{
                    'in':1,
                    'out':1}})
    return api 



class SNMPTest(unittest.TestCase):

    @mock.patch('scalrpy.stats_poller.netsnmp')
    def test_get(self, netsnmp):
        connection_info = {'host':'localhost', 'port':161,
                           'community':'YaOtBabuhkiYhelYaOtDeduhkiYhel'}
        with mock.patch('scalrpy.stats_poller.netsnmp.Session') as Session:
            instance = Session.return_value
            instance.get.return_value = (
                '0', '0', '0', '0', '0.0', '0.0', '0.0','0',
                '0', '0', '0', '0', '0', '0', '0', '0', '0')
            snmp = stats_poller.SNMP()
            data = snmp.get(connection_info, ['cpu', 'la', 'mem', 'net'])
            assert data == {
                    'cpu':{
                            'user':0.0,
                            'nice':0.0,
                            'system':0.0,
                            'idle':0.0},
                    'la':{
                            'la1':0.0,
                            'la5':0.0,
                            'la15':0.0},
                    'mem':{
                            'swap':0.0,
                            'swapavail':0.0,
                            'total':0.0,
                            'avail':0.0,
                            'free':0.0,
                            'shared':0.0,
                            'buffer':0.0,
                            'cached':0.0},
                    'net':{
                            'in':0.0,
                            'out':0.0}}



class ScalarizrAPITest(unittest.TestCase):

    def test_get(self):
        connection_info = {'host':'localhost', 'port':8010,
                           'key':'YaOtBabuhkiYhelYaOtDeduhkiYhel'}
        with mock.patch('scalrpy.stats_poller.HttpServiceProxy') as HttpServiceProxy:
            hsp = HttpServiceProxy.return_value
            hsp.sysinfo.cpu_stat.return_value = {'user':0, 'nice':0, 'system':0, 'idle':0}
            hsp.sysinfo.mem_info.return_value = {
                    'total_swap':0,
                    'avail_swap':0,
                    'total_real':0,
                    'total_free':0,
                    'shared':0,
                    'buffer':0,
                    'cached':0}
            hsp.sysinfo.net_stats.return_value = {
                    'eth0':{'receive':{'bytes':0}, 'transmit':{'bytes':0}}}
            hsp.sysinfo.load_average.return_value = [0.0, 0.0, 0.0]
            hsp.sysinfo.disk_stats.return_value = {
                'xvda1':{
                        'write':{
                                'num':0,
                                'bytes':0,
                                'sectors':0},
                        'read':{
                                'num':0,
                                'bytes':0,
                                'sectors':0}},
                'loop0':{
                        'write':{
                                'num':0,
                                'bytes':0,
                                'sectors':0},
                        'read':{
                                'num':0,
                                'bytes':0,
                                'sectors':0}}}
            api = stats_poller.ScalarizrAPI()
            data = api.get(connection_info, ['cpu', 'la', 'mem', 'net'])
            assert data == {
                    'cpu':{
                            'user':0.0,
                            'nice':0.0,
                            'system':0.0,
                            'idle':0.0},
                    'la':{
                            'la1':0.0,
                            'la5':0.0,
                            'la15':0.0},
                    'mem':{
                            'swap':0.0,
                            'swapavail':0.0,
                            'total':0.0,
                            'avail':0.0,
                            'free':0.0,
                            'shared':0.0,
                            'buffer':0.0,
                            'cached':0.0},
                    'net':{
                            'in':0.0,
                            'out':0.0}}



def test_server_thread():
    task = {'farm_id':'1', 'farm_role_id':'2', 'index':'3', 'host':'localhost', 'api_port':80,
            'api_key':'api_key', 'snmp_port':'80', 'community':'public',
            'metrics':['cpu', 'la', 'net', 'mem']}

    assert stats_poller.server_thread(task) == {'index': '3', 'farm_id': '1',
            'data': {'mem': {'swapavail': None, 'cached': None, 'free': None, 'avail': None,
            'buffer': None, 'swap': None, 'shared': None, 'total': None},
            'net': {'in': None, 'out': None},
            'cpu': {'idle': None, 'nice': None, 'system': None, 'user': None},
            'la': {'la5': None, 'la15': None, 'la1': None}}, 'farm_role_id': '2'}

    with mock.patch('scalrpy.stats_poller.HttpServiceProxy') as HttpServiceProxy:
        hsp = HttpServiceProxy.return_value
        hsp.sysinfo.cpu_stat.return_value = {'user':0, 'nice':0, 'system':0, 'idle':0}
        hsp.sysinfo.mem_info.return_value = {
                'total_swap':0,
                'avail_swap':0,
                'total_real':0,
                'total_free':0,
                'shared':0,
                'buffer':0,
                'cached':0}
        hsp.sysinfo.net_stats.return_value = {
                'eth0':{'receive':{'bytes':0}, 'transmit':{'bytes':0}}}
        hsp.sysinfo.load_average.return_value = [0.0, 0.0, 0.0]

        assert stats_poller.server_thread(task) == {'index': '3', 'farm_id': '1', 'data': {
                'mem': {'avail': 0.0, 'cached': 0.0, 'total': 0.0, 'swap': 0.0, 'buffer': 0.0,
                'shared': 0.0, 'swapavail': 0.0, 'free': 0.0}, 'net': {'out': 0.0, 'in': 0.0},
                'cpu': {'system': 0.0, 'idle': 0.0, 'user': 0.0, 'nice': 0.0},
                'la': {'la5': 0.0, 'la15': 0.0, 'la1': 0.0}}, 'farm_role_id': '2'}

    with mock.patch('scalrpy.stats_poller.netsnmp.Session') as Session:
        instance = Session.return_value
        instance.get.return_value = (
            '0', '0', '0', '0', '0.0', '0.0', '0.0','0',
            '0', '0', '0', '0', '0', '0', '0', '0', '0')

        assert stats_poller.server_thread(task) == {'index': '3', 'farm_id': '1', 'data': {
                'mem': {'avail': 0.0, 'cached': 0.0, 'total': 0.0, 'swap': 0.0, 'buffer': 0.0,
                'shared': 0.0, 'swapavail': 0.0, 'free': 0.0}, 'net': {'out': 0.0, 'in': 0.0},
                'cpu': {'system': 0.0, 'idle': 0.0, 'user': 0.0, 'nice': 0.0},
                'la': {'la5': 0.0, 'la15': 0.0, 'la1': 0.0}}, 'farm_role_id': '2'}



def test_post_processing():
    results = [] 
    results.append({'index': '1', 'farm_id': '1', 'data': {
            'mem': {'avail': 1.0, 'cached': 1.0, 'total': 1.0, 'swap': 1.0, 'buffer': 1.0,
            'shared': 1.0, 'swapavail': 1.0, 'free': 1.0}, 'net': {'out': 1.0, 'in': 1.0},
            'cpu': {'system': 1.0, 'idle': 1.0, 'user': 1.0, 'nice': 1.0}, 'la': {'la5': 1.0,
            'la15': 1.0, 'la1': 1.0}}, 'farm_role_id': '1'})
    results.append({'index': '2', 'farm_id': '1', 'data': {
            'mem': {'avail': 2.0, 'cached': 2.0, 'total': 2.0, 'swap': 2.0, 'buffer': 2.0,
            'shared': 2.0, 'swapavail': 2.0, 'free': 2.0}, 'net': {'out': 2.0, 'in': 2.0},
            'cpu': {'system': 2.0, 'idle': 2.0, 'user': 2.0, 'nice': 2.0}, 'la': {'la5': 2.0,
            'la15': 2.0, 'la1': 2.0}}, 'farm_role_id': '1'})
    results.append({'index': '3', 'farm_id': '1', 'data': {
            'mem': {'avail': 3.0, 'cached': 3.0, 'total': 3.0, 'swap': 3.0, 'buffer': 3.0,
            'shared': 3.0, 'swapavail': 3.0, 'free': 3.0}, 'net': {'out': 3.0, 'in': 3.0},
            'cpu': {'system': 3.0, 'idle': 3.0, 'user': 3.0, 'nice': 3.0}, 'la': {'la5': 3.0,
            'la15': 3.0, 'la1': 3.0}}, 'farm_role_id': '1'})
    results.append({'index': '1', 'farm_id': '1', 'data': {
            'mem': {'avail': 1.0, 'cached': 1.0, 'total': 1.0, 'swap': 1.0, 'buffer': 1.0,
            'shared': 1.0, 'swapavail': 1.0, 'free': 1.0}, 'net': {'out': 1.0, 'in': 1.0},
            'cpu': {'system': 1.0, 'idle': 1.0, 'user': 1.0, 'nice': 1.0}, 'la': {'la5': 1.0,
            'la15': 1.0, 'la1': 1.0}}, 'farm_role_id': '2'})
    results.append({'index': '1', 'farm_id': '2', 'data': {
            'mem': {'avail': 1.0, 'cached': 1.0, 'total': 1.0, 'swap': 1.0, 'buffer': 1.0,
            'shared': 1.0, 'swapavail': 1.0, 'free': 1.0}, 'net': {'out': 1.0, 'in': 1.0},
            'cpu': {'system': 1.0, 'idle': 1.0, 'user': 1.0, 'nice': 1.0}, 'la': {'la5': 1.0,
            'la15': 1.0, 'la1': 1.0}}, 'farm_role_id': '1'})

    ra, fa, rs, fs = stats_poller.post_processing(results)

    assert ra == {'2/1': {'mem': {'total': 1.0, 'buffer': 1.0, 'free': 1.0, 'avail': 1.0,
            'cached': 1.0, 'swap': 1.0, 'shared': 1.0, 'swapavail': 1.0}, 'net': {'out': 1.0,
            'in': 1.0}, 'cpu': {'user': 1.0, 'idle': 1.0, 'system': 1.0, 'nice': 1.0},
            'la': {'la5': 1.0, 'la15': 1.0, 'la1': 1.0}}, '1/2': {'mem': {'total': 1.0,
            'buffer': 1.0, 'free': 1.0, 'avail': 1.0, 'cached': 1.0, 'swap': 1.0, 'shared': 1.0,
            'swapavail': 1.0}, 'net': {'out': 1.0, 'in': 1.0}, 'cpu': {'user': 1.0, 'idle': 1.0,
            'system': 1.0, 'nice': 1.0}, 'la': {'la5': 1.0, 'la15': 1.0, 'la1': 1.0}},
            '1/1': {'mem': {'total': 2.0, 'buffer': 2.0, 'free': 2.0, 'avail': 2.0, 'cached': 2.0,
            'swap': 2.0, 'shared': 2.0, 'swapavail': 2.0}, 'net': {'out': 2.0, 'in': 2.0},
            'cpu': {'user': 2.0, 'idle': 2.0, 'system': 2.0, 'nice': 2.0}, 'la': {'la5': 2.0,
            'la15': 2.0, 'la1': 2.0}}}

    assert fa == {'1': {'mem': {'total': 1.75, 'buffer': 1.75, 'free': 1.75, 'avail': 1.75,
            'cached': 1.75, 'swap': 1.75, 'shared': 1.75, 'swapavail': 1.75}, 'net': {'out': 1.75,
            'in': 1.75}, 'cpu': {'user': 1.75, 'idle': 1.75, 'system': 1.75, 'nice': 1.75},
            'la': {'la5': 1.75, 'la15': 1.75, 'la1': 1.75}}, '2': {'mem': {'total': 1.0,
            'buffer': 1.0, 'free': 1.0, 'avail': 1.0, 'cached': 1.0, 'swap': 1.0, 'shared': 1.0,
            'swapavail': 1.0}, 'net': {'out': 1.0, 'in': 1.0}, 'cpu': {'user': 1.0, 'idle': 1.0,
            'system': 1.0, 'nice': 1.0}, 'la': {'la5': 1.0, 'la15': 1.0, 'la1': 1.0}}}

    assert rs == {'2/1': {'servers': {'s_running': 1}}, '1/2': {'servers': {'s_running': 1}},
                  '1/1': {'servers': {'s_running': 3}}}

    assert fs == {'1': {'servers': {'s_running': 4}}, '2': {'servers': {'s_running': 1}}}



class RRDWriterTest(unittest.TestCase):

    # todo
    def test_create_db(self):
        pass

    @mock.patch('scalrpy.stats_poller.rrdtool')
    @mock.patch('scalrpy.stats_poller.os')
    def test_write(self, os, rrdtool):
        writer = stats_poller.RRDWriter(stats_poller.net_source, stats_poller.net_archive)

        writer.write('/tmp/unittest', {'in':1, 'out':1})
        rrdtool.update.assert_called_with('/tmp/unittest', '--daemon',
                'unix:/var/run/rrdcached.sock', 'N:1:1')

        writer.write('/tmp/unittest', {'in':None, 'out':None})
        rrdtool.update.assert_called_with('/tmp/unittest', '--daemon',
                'unix:/var/run/rrdcached.sock', 'N:None:None')



class RRDWorkerTest(unittest.TestCase):

    def setUp(self):
        self.rrd_wrk = stats_poller.RRDWorker()
        self.rrd_wrk.writers['cpu'] = mock.Mock()
        self.rrd_wrk.writers['net'] = mock.Mock()
        self.rrd_wrk.writers['mem'] = mock.Mock()
        self.rrd_wrk.writers['la'] = mock.Mock()
        self.rrd_wrk.writers['servers'] = mock.Mock()


    def test_process_server_task(self):
        rrd_task = {'1/1/1':{
                'cpu':{
                    'user':1,
                    'system':1,
                    'nice':1,
                    'idle':1},
                'mem':{
                    'swap':1,
                    'swapavail':1,
                    'total':1,
                    'avail':1,
                    'free':1,
                    'shared':1,
                    'buffer':1,
                    'cached':1},
                'la':{
                    'la1':1.0,
                    'la5':1.0,
                    'la15':1.0},
                'net':{
                    'in':1,'out':1}}}

        self.rrd_wrk._process_server_task(rrd_task)

        self.rrd_wrk.writers['cpu'].write.assert_called_with(
                '/tmp/rrd_db_dir/x1x6/1/INSTANCE_1_1/CPUSNMP/db.rrd',
                {'user':1, 'system':1, 'nice':1, 'idle':1})
        self.rrd_wrk.writers['mem'].write.assert_called_with(
                '/tmp/rrd_db_dir/x1x6/1/INSTANCE_1_1/MEMSNMP/db.rrd',
                {'swap':1, 'swapavail':1, 'total':1, 'avail':1, 'free':1,
                'shared':1, 'buffer':1, 'cached':1})
        self.rrd_wrk.writers['net'].write.assert_called_with(
                '/tmp/rrd_db_dir/x1x6/1/INSTANCE_1_1/NETSNMP/db.rrd',
                {'in':1, 'out':1})
        self.rrd_wrk.writers['la'].write.assert_called_with(
                '/tmp/rrd_db_dir/x1x6/1/INSTANCE_1_1/LASNMP/db.rrd',
                {'la1':1.0, 'la5':1.0, 'la15':1.0})


    def test_process_ra_task(self):
        rrd_task = {'1/1':{
                'cpu':{
                    'user':1,
                    'system':1,
                    'nice':1,
                    'idle':1},
                'mem':{
                    'swap':1,
                    'swapavail':1,
                    'total':1,
                    'avail':1,
                    'free':1,
                    'shared':1,
                    'buffer':1,
                    'cached':1},
                'la':{
                    'la1':1.0,
                    'la5':1.0,
                    'la15':1.0},
                'net':{
                    'in':1,'out':1}}}

        self.rrd_wrk._process_ra_task(rrd_task)

        self.rrd_wrk.writers['cpu'].write.assert_called_with(
                '/tmp/rrd_db_dir/x1x6/1/FR_1/CPUSNMP/db.rrd',
                {'user':1, 'system':1, 'nice':1, 'idle':1})
        self.rrd_wrk.writers['mem'].write.assert_called_with(
                '/tmp/rrd_db_dir/x1x6/1/FR_1/MEMSNMP/db.rrd',
                {'swap':1, 'swapavail':1, 'total':1, 'avail':1, 'free':1,
                'shared':1, 'buffer':1, 'cached':1})
        self.rrd_wrk.writers['net'].write.assert_called_with(
                '/tmp/rrd_db_dir/x1x6/1/FR_1/NETSNMP/db.rrd',
                {'in':1, 'out':1})
        self.rrd_wrk.writers['la'].write.assert_called_with(
                '/tmp/rrd_db_dir/x1x6/1/FR_1/LASNMP/db.rrd',
                {'la1':1.0, 'la5':1.0, 'la15':1.0})

    def test_process_fa_task(self):
        rrd_task = {'1':{
                'cpu':{
                    'user':1,
                    'system':1,
                    'nice':1,
                    'idle':1},
                'mem':{
                    'swap':1,
                    'swapavail':1,
                    'total':1,
                    'avail':1,
                    'free':1,
                    'shared':1,
                    'buffer':1,
                    'cached':1},
                'la':{
                    'la1':1.0,
                    'la5':1.0,
                    'la15':1.0},
                'net':{
                    'in':1,'out':1}}}

        self.rrd_wrk._process_fa_task(rrd_task)

        self.rrd_wrk.writers['cpu'].write.assert_called_with(
                '/tmp/rrd_db_dir/x1x6/1/FARM/CPUSNMP/db.rrd',
                {'user':1, 'system':1, 'nice':1, 'idle':1})
        self.rrd_wrk.writers['mem'].write.assert_called_with(
                '/tmp/rrd_db_dir/x1x6/1/FARM/MEMSNMP/db.rrd',
                {'swap':1, 'swapavail':1, 'total':1, 'avail':1, 'free':1,
                'shared':1, 'buffer':1, 'cached':1})
        self.rrd_wrk.writers['net'].write.assert_called_with(
                '/tmp/rrd_db_dir/x1x6/1/FARM/NETSNMP/db.rrd',
                {'in':1, 'out':1})
        self.rrd_wrk.writers['la'].write.assert_called_with(
                '/tmp/rrd_db_dir/x1x6/1/FARM/LASNMP/db.rrd',
                {'la1':1.0, 'la5':1.0, 'la15':1.0})

    def test_process_rs_task(self):
        rrd_task = {'1/1':{'servers':{'s_running':1}}}

        self.rrd_wrk._process_rs_task(rrd_task)

        self.rrd_wrk.writers['servers'].write.assert_called_with(
                '/tmp/rrd_db_dir/x1x6/1/FR_1/SERVERS/db.rrd',
                {'s_running':1})

    def test_process_fs_task(self):
        rrd_task = {'1':{'servers':{'s_running':1}}}

        self.rrd_wrk._process_fs_task(rrd_task)

        self.rrd_wrk.writers['servers'].write.assert_called_with(
                '/tmp/rrd_db_dir/x1x6/1/FARM/SERVERS/db.rrd',
                {'s_running':1})



if __name__ == "__main__":
	unittest.main()
