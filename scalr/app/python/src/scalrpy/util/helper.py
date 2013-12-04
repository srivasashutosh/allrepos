
import os
import sys
import psutil
import logging
import logging.handlers


def configure_log(log_name, log_level=0, log_file=None, log_size=1024*10):
    level = {
            0:logging.CRITICAL,
            1:logging.ERROR,
            2:logging.WARNING,
            3:logging.INFO,
            4:logging.DEBUG}

    if log_level not in level.keys():
        sys.stderr.write('Wrong logging level. Set DEBUG\n')
        log_level = 4

    logger = logging.getLogger(log_name)
    logger.setLevel(level[log_level])
    frmtr= logging.Formatter('%(asctime)s-%(name)s-%(levelname)s# %(message)s')

    hndlr = logging.StreamHandler(sys.stderr)
    hndlr.setLevel(level[log_level])
    hndlr.setFormatter(frmtr)
    logger.addHandler(hndlr)

    if log_file:
        hndlr = logging.handlers.RotatingFileHandler(log_file, mode='a', maxBytes=log_size)
        hndlr.setLevel(level[log_level])
        hndlr.setFormatter(frmtr)
        logger.addHandler(hndlr)


def check_pid(pid_file):
    if os.path.exists(pid_file):
        pid = open(pid_file).read().strip()
        if pid and os.path.exists('/proc/' + pid):
            return False

    with open(pid_file, 'w+') as fp:
        fp.write(str(os.getpid()))
    return True


def kill_ps(pid, child=False):
    parent = psutil.Process(pid)
    if child:
        for child in parent.get_children(recursive=True):
            child.kill()
    parent.kill()

