<?php
use Scalr\DependencyInjection\Container;

class Scalr_UI_Request
{
    protected
        $params = array(),
        $definitions = array(),
        $requestParams = array(),
        $requestHeaders = array(),
        $user,
        $environment,
        $requestType,
        $paramErrors = array(),
        $paramsIsValid = true,
        $clientIp = null;

    public $requestApiVersion;

    const REQUEST_TYPE_UI = 'ui';
    const REQUEST_TYPE_API = 'api';

    /**
     *
     * @var Scalr_UI_Request
     */
    private static $_instance = null;

    /**
     * @return Scalr_UI_Request
     * @throws Scalr_Exception_Core
     */
    public static function getInstance()
    {
        if (self::$_instance === null)
            throw new Scalr_Exception_Core('Scalr_UI_Request not initialized');

        return self::$_instance;
    }

    public function __construct($type)
    {
        $this->requestType = $type;
        $this->requestHeaders = apache_request_headers();
    }

    public static function initializeInstance($type, $userId, $envId)
    {
        if (self::$_instance)
            self::$_instance = null;

        $instance = new Scalr_UI_Request($type);

        if ($userId) {
            try {
                $user = Scalr_Account_User::init();
                $user->loadById($userId);
            } catch (Exception $e) {
                throw new Exception('User account is no longer available.');
            }

            if ($user->status != Scalr_Account_User::STATUS_ACTIVE)
                throw new Exception('User account has been deactivated. Please contact your account owner.');

            if ($user->getType() != Scalr_Account_User::TYPE_SCALR_ADMIN) {
                $environment = $user->getDefaultEnvironment($envId);
                $user->getPermissions()->setEnvironmentId($environment->id);
            }

            if ($user->getAccountId()) {
                if ($user->getAccount()->status == Scalr_Account::STATUS_INACIVE) {
                    if ($user->getType() == Scalr_Account_User::TYPE_TEAM_USER)
                        throw new Exception('Scalr account has been deactivated. Please contact scalr team.');
                } else if ($user->getAccount()->status == Scalr_Account::STATUS_SUSPENDED) {
                    if ($user->getType() == Scalr_Account_User::TYPE_TEAM_USER)
                        throw new Exception('Account was suspended. Please contact your account owner to solve this situation.');
                }
            }

            // check header's variables
            $headerUserId = !is_null($instance->getHeaderVar('UserId')) ? intval($instance->getHeaderVar('UserId')) : null;
            $headerEnvId = !is_null($instance->getHeaderVar('EnvId')) ? intval($instance->getHeaderVar('EnvId')) : null;

            if (!empty($headerUserId) && $headerUserId != $user->getId())
                throw new Scalr_Exception_Core('Session expired. Please refresh page.', 1);

            if (!empty($headerEnvId) && !empty($environment) && $headerEnvId != $environment->id)
                throw new Scalr_Exception_Core('Session expired. Please refresh page.', 1);

            $instance->user = $user;
            $instance->environment = $environment;
        }

        $container = Scalr::getContainer();
        $container->request = $instance;

        self::$_instance = $instance;
        return $instance;
    }

    /**
     *
     * @return Scalr_Account_User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     *
     * @return Scalr_Environment
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    public function getRequestType()
    {
        return $this->requestType;
    }

    public function getHeaderVar($name)
    {
        $name = "X-Scalr-{$name}";
        return isset($this->requestHeaders[$name]) ? $this->requestHeaders[$name] : NULL;
    }

    public function defineParams($defs)
    {
        foreach ($defs as $key => $value) {
            if (is_array($value))
                $this->definitions[$key] = $value;

            if (is_string($value))
                $this->definitions[$value] = array();
        }

        $this->params = array();
    }

    public function getRequestParam($key)
    {
        $key = str_replace('.', '_', $key);

        if (isset($this->requestParams[$key]))
            return $this->requestParams[$key];
        else
            return NULL;
    }

    public function hasParam($key)
    {
        return isset($this->requestParams[$key]);
    }

    public function getRemoteAddr()
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    public function setParam($key, $value)
    {
        $this->requestParams[$key] = $value;
        $this->params[$key] = $value;
    }

    public function setParams($params)
    {
        $this->requestParams = array_merge($this->requestParams, $params);
    }

    public function getParam($key)
    {
        $value = null;
        if (isset($this->params[$key]))
            return $this->params[$key];

        if (isset($this->definitions[$key])) {
            $value = $this->getRequestParam($key);
            $rule = $this->definitions[$key];

            if ($value == NULL && isset($rule['default'])) {
                $value = $rule['default'];
            } else {
                switch (isset($rule['type']) ? $rule['type'] : null) {
                    case 'integer':
                    case 'int':
                        $value = intval($value);
                        break;

                    case 'bool':
                        $value = ($value == 'true' || $value == 'false') ?
                            ($value == 'true' ? true : false) : (bool) $value;
                        break;

                    case 'json':
                        $value = is_array($value) ? $value : json_decode($value, true);
                        break;

                    case 'array':
                        settype($value, 'array');
                        break;

                    case 'string':
                    default:
                        $value = strval($value);
                        break;
                }
            }

            $this->params[$key] = $value;

            return $value;
        }

        $this->params[$key] = $this->getRequestParam($key);

        return $this->params[$key];
    }

    public function getParams()
    {
        foreach ($this->definitions as $key => $value) {
            $this->getParam($key);
        }

        return $this->params;
    }

    /**
     *
     * @return Scalr_UI_Request
     */
    public function validate()
    {
        $this->paramErrors = array();
        $this->paramsIsValid = true;
        $validator = new Scalr_Validator();

        foreach ($this->definitions as $key => $value) {
            if (isset($value['validator'])) {
                $result = $validator->validate($this->getParam($key), $value['validator']);
                if ($result !== true)
                    $this->addValidationErrors($key, $result);
            }
        }

        if (count($this->paramErrors))
            $this->paramsIsValid = false;

        return $this;
    }

    public function isValid()
    {
        return $this->paramsIsValid;
    }

    public function addValidationErrors($field, $errors)
    {
        $this->paramsIsValid = false;
        if (! isset($this->paramErrors[$field]))
            $this->paramErrors[$field] = array();

        $this->paramErrors[$field] = array_merge($this->paramErrors[$field], $errors);
    }

    public function getValidationErrors()
    {
        return array('errors' => $this->paramErrors);
    }

    public function getValidationErrorsMessage()
    {
        $message = '';
        foreach ($this->paramErrors as $key => $value) {
            $message .= "Field '{$key}' has following errors: <ul>";
            foreach ($value as $error)
                $message .= "<li>{$error}</li>";
            $message .= "</ul>";
        }

        return $message;
    }

    /**
     * Gets client ip address
     *
     * @return string Returns client ip address.
     */
    public static function getClientIpAddress()
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * Gets client ip address for the current request
     *
     * @returns string Returns client ip address for the current request.
     */
    public function getClientIp()
    {
        if ($this->clientIp === null) {
            $this->clientIp = self::getClientIpAddress();
        }
        return $this->clientIp;
    }
}
