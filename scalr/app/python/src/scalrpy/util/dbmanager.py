
import urllib
import sqlsoup
import sqlalchemy

from sqlalchemy.orm import sessionmaker
from sqlalchemy.orm import scoped_session


class DBManager(object):
    """Database manager class"""

    def __init__(self, config):
        """
        :type config: dictionary
        :param config: Database connection info. Example:
            {
                'user':'user',
                'pass':'pass',
                'host':'localhost',
                'port':1234,
                'name':'scalr',
                'driver':'mysql+pymysql',
                'pool_recycle':120,
                'pool_size':4
            }
        """

        self.db = None

        try:
            host = '%s:%s' % (config['host'], int(config['port']))
        except:
            host = config['host']

        self.connection = '%s://%s:%s@%s/%s' % (
                    config['driver'],    
                    config['user'],      
                    urllib.quote_plus(config['pass']),      
                    host,
                    config['name'])
        self.kwargs = dict((k, v) for (k, v) in config.iteritems()\
                          if k not in ('user', 'pass', 'host', 'port', 'name', 'driver'))

    def get_db(self):
        if not self.db:
            self.db_engine = sqlalchemy.create_engine(self.connection, **self.kwargs)
            self.db = sqlsoup.SQLSoup(self.db_engine,
                    session=scoped_session(sessionmaker(bind=self.db_engine)))
        return self.db

