<?php

class Scalr_UI_Controller
{
    /**
     * @var \ADODB_mysqli
     */
    public $db;

    /**
     * @var Scalr_UI_Request
     */
    public $request;

    /**
     * @var Scalr_UI_Response
     */
    public $response;

    /**
     * @var Scalr_Account_User
     */
    public $user;

    /**
     * @var Scalr_Account
     */
    //public $account;

    /**
     * @var Scalr_Environment
     */
    protected $environment;

    /**
     * DI Container
     *
     * @var \Scalr\DependencyInjection\Container
     */
    private $container;

    /**
     * @var string
     */
    public $uiCacheKeyPattern;

    public function __construct()
    {
        $this->request = Scalr_UI_Request::getInstance();
        $this->response = Scalr_UI_Response::getInstance();
        $this->user = $this->request->getUser();
        $this->environment = $this->request->getEnvironment();
        $this->container = Scalr::getContainer();
        $this->db = Scalr::getDb();
    }

    public function init()
    {
    }

    /**
     * @return Scalr_Util_CryptoTool
     */
    protected function getCrypto()
    {
        if (! $this->crypto) {
            $this->crypto = new Scalr_Util_CryptoTool(MCRYPT_TRIPLEDES, MCRYPT_MODE_CFB, 24, 8);
            $this->cryptoKey = @file_get_contents(dirname(__FILE__)."/../../../etc/.cryptokey");
        }

        return $this->crypto;
    }

    public function getEnvironmentId()
    {
        if ($this->environment)
            return $this->environment->id;
        else
            throw new Scalr_Exception_Core("No environment defined for current session");
    }

    public function getEnvironment()
    {
        return $this->environment;
    }

    public function getParam($key)
    {
        return $this->request->getParam($key);
    }

    public function hasAccess()
    {
        if ($this->user) {
            // check admin, non-admin
            if ($this->user->getType() != Scalr_Account_User::TYPE_SCALR_ADMIN) {
                // check controller in permissions
                return true;
            } else
                return false;
        } else
            return false;
    }

    protected $sortParams = array();
    protected function sort($item1, $item2)
    {
        foreach ($this->sortParams as $cond) {
            $field = $cond['property'];
            if (is_int($item1[$field]) || is_float($item1[$field])) {
                $result = ($item1[$field] == $item2[$field]) ? 0 : (($item1[$field] < $item2[$field]) ? -1 : 1);
            } else {
                $result = strcmp($item1[$field], $item2[$field]);
            }
            if ($result != 0)
                return $cond['direction'] == 'DESC' ? $result : ($result > 0 ? -1: 1);
        }

        return 0;
    }

    protected function buildResponseFromData(array $data, $filterFields = array())
    {
        $this->request->defineParams(array(
            'start' => array('type' => 'int', 'default' => 0),
            'limit' => array('type' => 'int', 'default' => 20)
        ));

        if ($this->getParam('query') && count($filterFields) > 0) {
            $query = trim($this->getParam('query'));
            foreach ($data as $k => $v) {
                $found = false;
                foreach ($filterFields as $field)
                {
                    if (stristr($v[$field], $query)) {
                        $found = true;
                        break;
                    }
                }

                if (!$found)
                    unset($data[$k]);
            }
        }

        $response['total'] = count($data);

        $s = $this->getParam('sort');
        if (! is_array($s)) {
            $s = json_decode($this->getParam('sort'), true);
        }

        if (is_array($s)) {
            $sorts = array();
            if (count($s) && !is_array($s[0]))
                $s = array($s);

            foreach ($s as $param) {
                $sort = preg_replace("/[^A-Za-z0-9_]+/", "", $param['property']);
                $dir = (in_array(strtolower($param['direction']), array('asc', 'desc'))) ? $param['direction'] : 'ASC';

                $sortParams[] = array('property' => $sort, 'direction' => $dir);
            }
        } else if ($this->getParam('sort')) {
            $sort = preg_replace("/[^A-Za-z0-9_]+/", "", $this->getParam('sort'));
            $dir = (in_array(strtolower($this->getParam('dir')), array('asc', 'desc'))) ? $this->getParam('dir') : 'ASC';

            $sortParams[] = array('property' => $sort, 'direction' => $dir);
        }

        if (count($sortParams)) {
            $this->sortParams = $sortParams;
            usort($data, array($this, 'sort'));
        }

        $data = (count($data) > $this->getParam('limit')) ? array_slice($data, $this->getParam('start'), $this->getParam('limit')) : $data;

        $response["success"] = true;
        $response['data'] = array_values($data);

        return $response;
    }

    protected function buildResponseFromSql2($sql, $sortFields = array(), $filterFields = array(), $args = array(), $noLimit = false)
    {
        if ($this->getParam('query') && count($filterFields) > 0) {
            $filter = $this->db->qstr('%' . trim($this->getParam('query')) . '%');
            foreach($filterFields as $field) {
                $fs = explode('.', $field);
                foreach($fs as &$f) {
                    $f = "`{$f}`";
                }
                $field = implode('.', $fs);
                $likes[] = "{$field} LIKE {$filter}";
            }
            $sql = str_replace(':FILTER:', '(' . implode(' OR ', $likes) . ')', $sql);
        } else {
            $sql = str_replace(':FILTER:', 'true', $sql);
        }

        if (!$noLimit) {
            $response['total'] = $this->db->GetOne('SELECT COUNT(*) FROM (' . $sql. ') c_sub', $args);
        }

        if (is_array($this->getParam('sort'))) {
            $sort = $this->getParam('sort');
            $sortSql = array();
            if (count($sort) && (!isset($sort[0]) || !is_array($sort[0])))
                $sort = array($sort);

            foreach ($sort as $param) {
                $property = preg_replace('/[^A-Za-z0-9_]+/', '', $param['property']);
                $direction = (in_array(strtolower($param['direction']), array('asc', 'desc'))) ? $param['direction'] : 'asc';

                if (in_array($property, $sortFields))
                    $sortSql[] = "`{$property}` {$direction}";
            }

            if (count($sortSql))
                $sql .= ' ORDER BY ' . implode($sortSql, ',');
        }

        if (! $noLimit) {
            $start = intval($this->getParam('start'));
            if ($start > $response["total"] || $start < 0)
                $start = 0;

            $limit = intval($this->getParam('limit'));
            if ($limit < 1 || $limit > 100)
                $limit = 100;
            $sql .= " LIMIT $start, $limit";
        }

        $response['success'] = true;
        $response['data'] = $this->db->GetAll($sql, $args);

        return $response;
    }

    protected function buildResponseFromSql($sql, $filterFields = array(), $groupSQL = "", $simpleQuery = true, $noLimit = false)
    {
        $this->request->defineParams(array(
            'start' => array('type' => 'int', 'default' => 0),
            'limit' => array('type' => 'int', 'default' => 20)
        ));

        if (is_array($groupSQL)) {
            return $this->buildResponseFromSql2($sql, $filterFields, $groupSQL, is_array($simpleQuery) ? $simpleQuery : array(), $noLimit);
        }

        if ($this->getParam('query') && count($filterFields) > 0) {
            $filter = $this->db->qstr('%' . trim($this->getParam('query')) . '%');
            foreach($filterFields as $field) {
                if ($simpleQuery)
                    $likes[] = "`{$field}` LIKE {$filter}";
                else
                    $likes[] = "{$field} LIKE {$filter}";
            }
            $sql .= " AND (";
            $sql .= implode(" OR ", $likes);
            $sql .= ")";
        }

        if ($groupSQL)
            $sql .= "{$groupSQL}";

        if (!$noLimit) {
            $response['total'] = $this->db->GetOne('SELECT COUNT(*) FROM (' . $sql. ') c_sub');
        }

        // @TODO replace with simple code (legacy code)
        $s = $this->getParam('sort');
        if (! is_array($s)) {
            $s = json_decode($this->getParam('sort'), true);
        }

        if (is_array($s)) {
            $sorts = array();
            if (count($s) && (!isset($s[0]) || !is_array($s[0])))
                $s = array($s);

            foreach ($s as $param) {
                $sort = preg_replace("/[^A-Za-z0-9_]+/", "", $param['property']);
                $dir = (in_array(strtolower($param['direction']), array('asc', 'desc'))) ? $param['direction'] : 'ASC';

                if ($sort && $dir)
                    $sorts[] = "`{$sort}` {$dir}";
            }

            if (count($sorts) > 0) {
                $sql .= " ORDER BY " . implode($sorts, ',');
            }
        } else if ($this->getParam('sort')) {
            $sort = preg_replace("/[^A-Za-z0-9_]+/", "", $this->getParam('sort'));
            $dir = (in_array(strtolower($this->getParam('dir')), array('asc', 'desc'))) ? $this->getParam('dir') : 'ASC';
            $sql .= " ORDER BY `{$sort}` {$dir}";
        }

        if (! $noLimit) {
            $start = intval($this->getParam('start'));
            if ($start > $response["total"])
                $start = 0;

            $limit = intval($this->getParam('limit'));
            $sql .= " LIMIT $start, $limit";
        }

        //$response['sql'] = $sql;
        $response["success"] = true;
        $response["data"] = $this->db->GetAll($sql);

        return $response;
    }

    public function call($pathChunks = array(), $permissionFlag = true)
    {
        $arg = array_shift($pathChunks);

        if ($this->user) {
            if ($this->getContainer()->config->get('scalr.auth_mode') != 'ldap') {
                if ($this->user->getType() == Scalr_Account_User::TYPE_TEAM_USER) {
                    if (!$this->user->isTeamUserInEnvironment($this->getEnvironmentId(), Scalr_Account_Team::PERMISSIONS_OWNER) &&
                        !$this->user->isTeamUserInEnvironment($this->getEnvironmentId(), Scalr_Account_Team::PERMISSIONS_FULL)
                    ) {
                        if (method_exists($this, 'getPermissionDefinitions')) {
                            // rules defined for this controller
                            $cls = get_class($this);
                            $clsShort = str_replace('Scalr_UI_Controller_', '', $cls);
                            $methodShort = str_replace('Action', '', $method); // TODO: check
                            $clsPermissions = $cls::getPermissionDefinitions();

                            $permissions = $this->user->getGroupPermissions($this->getEnvironmentId());
                            if (array_key_exists($clsShort, $permissions)) {
                                $perm = $permissions[$clsShort];

                                if (in_array('VIEW', $perm, true) || in_array('FULL', $perm, true))
                                    $permissionFlag = true;
                                else
                                    $permissionFlag = false;
                            } else
                                $permissionFlag = false;
                        }
                    }
                }
            }
        }

        try {
            $subController = self::loadController($arg, get_class($this), true);
        } catch (Scalr_UI_Exception_NotFound $e) {
            $subController = null;
        }

        if ($subController) {
            $this->addUiCacheKeyPatternChunk($arg);
            $subController->uiCacheKeyPattern = $this->uiCacheKeyPattern;
            $subController->call($pathChunks, $permissionFlag);

        } else if (($action = $arg . 'Action') && method_exists($this, $action)) {
            $this->addUiCacheKeyPatternChunk($arg);
            $this->response->setHeader('X-Scalr-Cache-Id', $this->uiCacheKeyPattern);

            if (! $permissionFlag)
                throw new Scalr_Exception_InsufficientPermissions();

            $this->callActionMethod($action);

        } else if (count($pathChunks) > 0) {
            $const = constant(get_class($this) . '::CALL_PARAM_NAME');
            if ($const) {
                $this->request->setParams(array($const => $arg));
                $this->addUiCacheKeyPatternChunk('{' . $const . '}');
            } else {
                // TODO notice
            }

            $this->call($pathChunks, $permissionFlag);

        } else if (method_exists($this, 'defaultAction') && $arg == '') {
            $this->response->setHeader('X-Scalr-Cache-Id', $this->uiCacheKeyPattern);

            if (! $permissionFlag)
                throw new Scalr_Exception_InsufficientPermissions();

            $this->callActionMethod('defaultAction');

        } else {
            throw new Scalr_UI_Exception_NotFound();
        }
    }

    public function callActionMethod($method)
    {
        if ($this->request->getRequestType() == Scalr_UI_Request::REQUEST_TYPE_API) {
            $apiMethodCheck = false;
            if (method_exists($this, 'getApiDefinitions')) {
                $api = $this::getApiDefinitions();
                $m = str_replace('Action', '', $method);
                if (in_array($m, $api)) {
                    $apiMethodCheck = true;
                }
            }

            if (! $apiMethodCheck)
                throw new Scalr_UI_Exception_NotFound();
        }

        if ($this->user) {
            if ($this->user->getType() == Scalr_Account_User::TYPE_TEAM_USER) {
                if ($this->getContainer()->config->get('scalr.auth_mode') != 'ldap') {
                    if (!$this->user->isTeamUserInEnvironment($this->getEnvironmentId(), Scalr_Account_Team::PERMISSIONS_OWNER) &&
                        !$this->user->isTeamUserInEnvironment($this->getEnvironmentId(), Scalr_Account_Team::PERMISSIONS_FULL)
                    ) {
                        if (method_exists($this, 'getPermissionDefinitions')) {
                            // rules defined for this controller
                            $cls = get_class($this);
                            $clsShort = str_replace('Scalr_UI_Controller_', '', $cls);
                            $methodShort = str_replace('Action', '', $method);
                            $clsPermissions = $cls::getPermissionDefinitions();

                            $permissions = $this->user->getGroupPermissions($this->getEnvironmentId());
                            if (array_key_exists($clsShort, $permissions)) {
                                // rules for user and such controller
                                $perm = $permissions[$clsShort];

                                if (! in_array('FULL', $perm, true)) {
                                    // user doesn't has full privilegies
                                    if (array_key_exists($methodShort, $clsPermissions)) {
                                        // standalone rule for this method
                                        if (! in_array($clsPermissions[$methodShort], $perm))
                                            throw new Scalr_Exception_InsufficientPermissions();
                                    } else {
                                        // VIEW rule
                                        if (! in_array('VIEW', $perm))
                                            throw new Scalr_Exception_InsufficientPermissions();
                                    }
                                }

                            } else
                                throw new Scalr_Exception_InsufficientPermissions();

                        }
                    }
                }
            }
        }

        /*
         * Debug action section
         * Controller::Action => array of filter's params (accountId, userId) or true
         */
        $debug = false;
        /*array(
            'Scalr_UI_Controller_Core::xChangeEnvironmentAction' => array('accountId' => array('7871', '263', '9159', '6412', '6957')),
            'Scalr_UI_Controller_Guest::xGetContextAction' => array('accountId' => array('7871', '263', '9159', '6412', '6957'))
        );*/
        $debugMode = false;
        $key = get_class($this) . '::' . $method;

        if ($debug && array_key_exists($key, $debug)) {
            $value = $debug[$key];

            if (is_array($value) && $this->user) {
                if (isset($value['accountId'])) {
                    if (is_array($value['accountId']) && in_array($this->user->getAccountId(), $value['accountId']))
                        $debugMode = true;

                    if (is_numeric($value['accountId']) && $value['accountId'] == $this->user->getAccountId())
                        $debugMode = true;
                }

                if (isset($value['userId'])) {
                    if (is_array($value['userId']) && in_array($this->user->getId(), $value['userId']))
                        $debugMode = true;

                    if (is_numeric($value['userId']) && $value['userId']== $this->user->getId())
                        $debugMode = true;
                }
            } else {
                $debugMode = true;
            }
        }

        if ($debugMode) {
            $this->response->debugLog('Server', $_SERVER);
            $this->response->debugLog('Request', $_REQUEST);
            $this->response->debugLog('Session', Scalr_Session::getInstance());
        }

        $this->{$method}();

        if ($debugMode) {
            if ($this->response->jsResponseFlag) {
                $this->response->debugLog('JS Response', $this->response->jsResponse);
            }

            try {
                $message = '';
                foreach($this->response->serverDebugLog as $value) {
                    $message .= $value['key'] . ":\n" . $value['value'] . "\n\n";
                }

                $this->db->Execute('INSERT INTO ui_debug_log (ipaddress, url, report, env_id, account_id, user_id) VALUES(?, ?, ?, ?, ?, ?)', array(
                    $this->request->getClientIp(),
                    $key,
                    $message,
                    $this->getEnvironment() ? $this->getEnvironmentId() : 0,
                    $this->user ? $this->user->getAccountId() : 0,
                    $this->user ? $this->user->getId() : 0
                ));

            } catch(Exception $e) {}
        }
    }

    public function addUiCacheKeyPatternChunk($chunk)
    {
        $this->uiCacheKeyPattern .= "/{$chunk}";
    }

    static public function handleRequest($pathChunks, $params)
    {
        $startTime = microtime(true);

        if ($pathChunks[0] == '')
            $pathChunks = array('guest');

        try {
            Scalr_UI_Request::getInstance()->setParams($params);
            $user = Scalr_UI_Request::getInstance()->getUser();
            $controller = self::loadController(array_shift($pathChunks), 'Scalr_UI_Controller', true);
            $class = get_class($controller);

            if (!$user && $class != 'Scalr_UI_Controller_Guest') {
                throw new Scalr_Exception_InsufficientPermissions();
            } else {
                $controller->uiCacheKeyPattern = '';

                if ($user &&
                    $user->getAccountId() &&
                    $user->getAccount()->status != Scalr_Account::STATUS_ACTIVE &&
                    $user->getType() == Scalr_Account_User::TYPE_ACCOUNT_OWNER &&
                    $class != 'Scalr_UI_Controller_Billing' &&
                    $class != 'Scalr_UI_Controller_Core' &&
                    $class != 'Scalr_UI_Controller_Guest' &&
                    $class != 'Scalr_UI_Controller_Environments'
                ) {
                    // suspended account, user = owner, replace controller with billing or allow billing action/guest action
                    $controller = self::loadController('Billing', 'Scalr_UI_Controller', true);
                    $r = explode('_', get_class($controller));
                    $controller->addUiCacheKeyPatternChunk(strtolower((array_pop($r))));
                    $controller->call();
                } else {
                    $r = explode('_', $class);
                    $controller->addUiCacheKeyPatternChunk(strtolower((array_pop($r))));
                    $controller->call($pathChunks);
                }
            }

        } catch (Scalr_UI_Exception_AccessDenied $e) {
            Scalr_UI_Response::getInstance()->setHttpResponseCode(403);

        } catch (Scalr_Exception_InsufficientPermissions $e) {
            if (is_object($user))
                Scalr_UI_Response::getInstance()->failure($e->getMessage());
            else
                Scalr_UI_Response::getInstance()->setHttpResponseCode(403);

        } catch (Scalr_UI_Exception_NotFound $e) {
            Scalr_UI_Response::getInstance()->setHttpResponseCode(404);

        } catch (ADODB_Exception $e) {
            try {
                $db = Scalr::getDb();
                $user = Scalr_UI_Request::getInstance()->getUser();

                $db->Execute('INSERT INTO ui_errors (tm, file, lineno, url, short, message, browser, account_id) VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE cnt = cnt + 1', array(
                    $e->getFile(),
                    $e->getLine(),
                    $_SERVER['REQUEST_URI'],
                    $e->getMessage(),
                    $e->getMessage() . "\n" . $e->getTraceAsString(),
                    $_SERVER['HTTP_USER_AGENT'],
                    $user ? $user->getAccountId() : ''
                ));

                Scalr_UI_Response::getInstance()->failure('Database error (1)');

            } catch (Exception $e) {
                Scalr_UI_Response::getInstance()->failure('Database error (2)');
            }

        } catch (Exception $e) {
            Scalr_UI_Response::getInstance()->failure($e->getMessage());
        }

        Scalr_UI_Response::getInstance()->setHeader("X-Scalr-ActionTime", microtime(true) - $startTime);
        Scalr_UI_Response::getInstance()->sendResponse();
    }

    /**
     *
     * @return Scalr_UI_Controller
     * @throws Scalr_UI_Exception_NotFound
     * @throws Scalr_Exception_InsufficientPermissions
     */
    static public function loadController($controller, $prefix = 'Scalr_UI_Controller', $checkPermissions = false)
    {
        if (preg_match("/^[a-z0-9]+$/i", $controller)) {
            $controller = ucwords(strtolower($controller));

            // support versioning
            if ($prefix == 'Scalr_UI_Controller' && $controller == 'Account') {
                $request = Scalr_UI_Request::getInstance();
                if ($request->getRequestType() == Scalr_UI_Request::REQUEST_TYPE_UI) {
                    $controller = 'Account2';
                } else if ($request->getRequestType() == Scalr_UI_Request::REQUEST_TYPE_API && $request->requestApiVersion == '2') {
                    $controller = 'Account2';
                }
            }

            $className = "{$prefix}_{$controller}";

            if (file_exists(SRCPATH . '/' . str_replace('_', '/', $prefix) . '/' . $controller . '.php') && class_exists($className)) {
                $o = new $className();
                $o->init();
                if (!$checkPermissions || $o->hasAccess())
                    return $o;
                else
                    throw new Scalr_Exception_InsufficientPermissions();
            }
        }

        throw new Scalr_UI_Exception_NotFound(isset($className) ? $className : '');
    }

    /**
     * Gets DI Container
     *
     * @return \Scalr\DependencyInjection\Container Returns DI Container
     */
    public function getContainer()
    {
        return $this->container;
    }
}
