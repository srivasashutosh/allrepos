<?php

class Modules_Platform
{

    protected $platform;

    /**
     * DI Container
     *
     * @var Scalr\DependencyInjection\Container
     */
    protected $container;

    /**
     * @var ADODB_mysqli
     */
    protected $db;

    function __construct($platform = null)
    {
        $this->platform = $platform;
        $this->container = Scalr::getContainer();
        $this->db = $this->container->adodb;
    }

    function getConfigVariable($name, $env, $encrypted = true, $cloudLocation = '')
    {
        $name = "{$this->platform}.{$name}";

        return $env->getPlatformConfigValue($name, $encrypted, $cloudLocation);
    }

    function setConfigVariable($pars, $env, $encrypted = true, $cloudLocation = '')
    {
        $config = array();

        foreach ($pars as $key => $v)
            $config["{$this->platform}.{$key}"] = $v;

        $env->setPlatformConfig($config, $encrypted, $cloudLocation);
    }
}
