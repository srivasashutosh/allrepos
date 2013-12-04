
import os
import sys
import yaml
import time
import shutil
import logging
import argparse

from scalrpy.util import helper
from scalrpy.util import dbmanager

from sqlalchemy import exc as db_exc

import scalrpy

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
ETC_DIR = os.path.abspath(BASE_DIR + '/../../../etc')

config = {
    'log_file':'/var/log/scalr.stats-cleaner.log'
}

logger = logging.getLogger(__file__)

def configure(args, cnf):
    global config

    for k, v in cnf.iteritems():
            config.update({k:v})

    for k, v in vars(args).iteritems():
        if v is not None:
            config.update({k:v})

    helper.configure_log(__file__, log_level=config['verbosity'], log_file=config['log_file'],
                         log_size=1024*100)


def clean():
    db_manager = dbmanager.DBManager(config['connections']['mysql'])
    db = db_manager.get_db()
    session = db.session

    try:
        db_farms = ['%s' % int(farm.id) for farm in session.query(db.farms.id).all()]
    except (db_exc.OperationalError, db_exc.InternalError):
        logger.critical(sys.exc_info())
        return

    for dir_ in os.listdir(config['rrd_db_dir']):
        for farm in os.listdir('%s/%s' % (config['rrd_db_dir'], dir_)):
            if farm not in db_farms:
                logger.debug('Delete farm %s' % farm )
                if not config['test']:
                    shutil.rmtree('%s/%s/%s'
                                  % (config['rrd_db_dir'], dir_, farm), ignore_errors=True)


def main():
    try:
        parser = argparse.ArgumentParser()

        parser.add_argument('-c', '--config_file', default='%s/config.yml' % ETC_DIR,
                            help='config file')
        parser.add_argument('-d', '--rrd_db_dir', default=None,
                            help='path to rrd database')
        parser.add_argument('-v', '--verbosity', action='count', default=1,
                            help='increase output verbosity [0:4]. Default is 1 - ERROR')
        parser.add_argument('-t', '--test', action='store_true', default=False, help='Test only')
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
        logger.info('Start time: %s' % time.ctime())
        clean()
    except Exception:
        logger.critical('Something happened and I think I died')
        logger.exception('Critical exception')
        sys.exit(1)


if __name__ == '__main__':
    main()

