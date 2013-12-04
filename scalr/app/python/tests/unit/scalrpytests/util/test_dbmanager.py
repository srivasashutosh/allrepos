
import unittest

from scalrpy.util import dbmanager


class DBManagerTest(unittest.TestCase):

    def setUp(self):
        self.config = {
                'user':'user',
                'pass':'pass',
                'host':'localhost',
                'port':10,
                'name':'scalr',
                'driver':'mysql+pymysql',
                'pool_recycle':120,
                'pool_size':4}


    def test_init(self):
        db_manager = dbmanager.DBManager(self.config)
        assert db_manager.connection == 'mysql+pymysql://user:pass@localhost:10/scalr'
        assert db_manager.kwargs == {'pool_recycle':120, 'pool_size':4}

        db = db_manager.get_db()
        assert db is not None
        assert db_manager.db is not None


    def test_get_db(self):
        db_manager = dbmanager.DBManager(self.config)
        assert db_manager.get_db() is not None

        db_manager.db = None
        assert db_manager.get_db() is not None


if __name__ == "__main__":
	unittest.main()
