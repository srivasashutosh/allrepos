
import os
import sys
import atexit
import logging

from scalrpy.util import helper


logger = logging.getLogger(__file__)
logger.setLevel(logging.DEBUG)
hndlr = logging.StreamHandler()
hndlr.setFormatter(logging.Formatter('%(asctime)s-%(name)s-%(levelname)s# %(message)s'))
hndlr.setLevel(logging.ERROR)
logger.addHandler(hndlr)


class BaseDaemon(object):
    """Base class for daemon"""

    def __init__(
                self, pid_file, logger_name=None,
                stdin='/dev/null',
                stdout='/dev/null',
                stderr='/dev/null'):

        self.pid_file = pid_file
        if logger_name:
            global logger
            logger = logging.getLogger('%s.%s' % (logger_name, os.path.basename(__file__)))
        self.stdin = stdin
        self.stdout = stdout
        self.stderr = stderr


    def start(self):
        logger.debug('Start')
        self.daemonize()
        self.run()


    def stop(self):
        logger.debug('Stop')
        try:
            pf = file(self.pid_file, 'r')
            pid = int(pf.read().strip())
            pf.close()
        except IOError:
            pid = None
        except ValueError:
            pid = None
            os.remove(self.pid_file)

        if not pid:
            message = "Pid file %s does not exist"
            logger.critical(message % self.pid_file)
            return

        helper.kill_ps(pid, child=True)

        if os.path.exists(self.pid_file):
            os.remove(self.pid_file)
            self.running = False


    def restart(self):
        logger.debug('Restart')
        self.stop()
        self.start()


    def run(self):
        """
        Override this method in derived class
        """
        pass


    def delpid(self):
        logger.debug('Remove pid file')
        os.remove(self.pid_file)


    def daemonize(self):
        try:
            # first fork
            pid = os.fork()
            if pid > 0:
                sys.exit(0)
        except OSError, e:
            logger.error(e)
            raise

        os.chdir('/')
        os.setsid()
        os.umask(0)

        try:
            # second fork
            pid = os.fork()
            if pid > 0:
                sys.exit(0)
        except OSError, e:
            logger.critical(e)
            raise

        atexit.register(self.delpid)
        pid = str(os.getpid())
        try:
            file(self.pid_file,'w+').write("%s\n" % pid)
        except Exception as e:
            logger.critical(e)
            raise

        # redirect standard file descriptors
        sys.stdout.flush()
        sys.stderr.flush()
        si = file(self.stdin, 'r')
        so = file(self.stdout, "a+")
        se = file(self.stderr, "a+", 0)
        os.dup2(si.fileno(), sys.stdin.fileno())
        os.dup2(so.fileno(), sys.stdout.fileno())
        os.dup2(se.fileno(), sys.stderr.fileno())

