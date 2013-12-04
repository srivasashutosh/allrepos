<?php

namespace Scalr\Db;

/**
 * Database connection pool [SCALRCORE-369]
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    27.05.2013
 */
class ConnectionPool
{
    /**
     * Connection pool
     *
     * @var array
     */
    private $pool = array();

    /**
     * Active PID
     *
     * @var string
     */
    private $currentpid;

    /**
     * Connection DSN
     *
     * @var string
     */
    private $dsn;

    /**
     * Whether multrithreading is defined
     *
     * @var bool
     */
    private $multithreading = false;

    /**
     * constructor
     */
    public function __construct($dsn)
    {
        $this->multithreading = defined('SCALR_MULTITHREADING');
        if ($this->multithreading) {
            $this->dsn = $dsn . (strpos($dsn, '?') === false ? '?' : '&') . 'new';
        } else {
            $this->dsn = $dsn;
        }
    }

    /**
     * Checks whether current connection is alive
     *
     * @param   ADODB_mysqli  $conn
     * @return  bool Returns true on success or false otherwise
     */
    private static function isConnectionAlive($conn)
    {
        $alive = true;
        if (!empty($conn->_connectionID) && method_exists($conn->_connectionID, 'ping')) {
            $alive = (bool) $conn->_connectionID->ping();
        } else {
            try {
                $conn->GetOne('SELECT 1');
            } catch (\ADODB_Exception $e) {
                if (stristr($e->getMessage(), 'has gone away') !== false) {
                    $alive = false;
                }
            }
        }
        return $alive;
    }

    /**
     * Gets connection to database as shared object
     *
     * @return \ADODB_mysqli
     */
    private function getConnection()
    {
        /* @var $conn \ADODB_mysqli */
        $params = array(
            'dsn' => $this->dsn,
        );
        if ($this->multithreading && function_exists("posix_getpid")) {
            $params['pid'] = posix_getpid();
            if (isset($this->currentpid)) {

                $conn = $this->pool[$this->currentpid];

                $alive = self::isConnectionAlive($conn);

                if (!$alive || $this->currentpid != $params['pid']) {
                    //Child proces has just started.
                    //We have to close database connection for the parent process.
                    //Parent process will initiate new connection if it's needed.
                    try {
                        if ($alive) {
                            $conn->Close();
                        }
                    } catch (\Exception $e) {
                    }
                    unset($this->pool[$this->currentpid]);
                    $this->currentpid = null;
                }

                unset($conn);
            }
        } else {
            $params['pid'] = 0;
        }

        if (!isset($this->currentpid)) {
            if (!function_exists('ADONewConnection'))
                throw new \Exception('Could not find ADODB library.');

            $conn = false;
            //Failover
            for ($i = 0; ($i < 3) && !$conn; ++$i) {
                try {
                    $conn = \ADONewConnection($params['dsn']);
                } catch (\ADODB_Exception $e) {
                    $exception = $e;
                }
            }

            if (!$conn && isset($exception)) {
                throw new \Exception("Could not establish database connection: {$exception->getMessage()}", E_ERROR);
            }

            if (!$conn || !$conn->IsConnected()) {
                throw new \Exception("Could not establish connection to database.", E_ERROR);
            }

            $conn->debug = false;
            $conn->cacheSecs = 0;
            $conn->SetFetchMode(ADODB_FETCH_ASSOC);

            $this->currentpid = $params['pid'];
            $this->pool[$this->currentpid] = $conn;
        }

        return $this->pool[$this->currentpid];
    }

    public function __isset($name)
    {
        return isset($this->getConnection()->$name);
    }

    public function __unset($name)
    {
        $conn = $this->getConnection();
        unset($conn->$name);
    }

    public function __get($name)
    {
        return $this->getConnection()->$name;
    }

    public function __set($name, $value)
    {
        return $this->getConnection()->$name = $value;
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array(array($this->getConnection(), $name), $arguments);
    }

    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array(get_class($this->getConnection()) . "::" . $name, $arguments);
    }
}