<?php

class Modules_Platforms_Aws
{

    /**
     * DI Containter
     *
     * @var \Scalr\DependencyInjection\Container
     */
    protected $container;

    /**
     * @var \ADODB_mysqli
     */
    protected $db;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->container = \Scalr::getContainer();
        $this->db = $this->container->adodb;
    }

    /**
     * Gets DI Container
     *
     * @return \Scalr\DependencyInjection\Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Gets the list of available locations
     *
     * @return  array Returns the list of available locations looks like array(location => description)
     */
    public function getLocations()
    {
        return array(
            'us-east-1'      => 'AWS / US East 1 (N. Virginia)',
            'us-west-1'      => 'AWS / US West 1 (N. California)',
            'us-west-2'      => 'AWS / US West 2 (Oregon)',
            'eu-west-1'      => 'AWS / EU West 1 (Ireland)',
            'sa-east-1'      => 'AWS / SA East 1 (Sao Paulo)',
            'ap-southeast-1' => 'AWS / Asia Pacific East 1 (Singapore)',
            'ap-southeast-2' => 'AWS / Asia Pacific East 2 (Sydney)',
            'ap-northeast-1' => 'AWS / Asia Pacific North 1 (Tokyo)'
        );
    }
}
