<?php

namespace Scalr\System\Config;

use Scalr\Util\ClosureInvoker;

/**
 * Extension
 *
 * This class helps to define default values of the config parameters.
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    11.06.2013
 */
class Extension implements \IteratorAggregate
{
    /**
     * Array of defined parameters
     *
     * It looks like array('dot.notation.name' => object)
     *
     * @var array
     */
    private $parameters = array();

    /**
     * Array of defined parameter bags
     *
     * @var array
     */
    public $paths = array();

    /**
     * Loads config defaults
     *
     * It's supposed to load all external Extension from here.
     *
     * @return  Extension
     */
    public function load()
    {
        $this->parameters = array();

        //Please follow alphabetical order when add something new
        $this
            ->sub('scalr')
                ->node('allowed_clouds', array('ec2', 'openstack', 'cloudstack', 'idcf', 'gce', 'eucalyptus',
                                               'rackspace', 'rackspacenguk', 'rackspacengus', 'nimbula'))

                ->sub('auditlog', false)
                    ->node('enabled', false)
                ->end()

                ->node('auth_mode')

                ->sub('aws')
                    ->node('security_group_name', 'scalr.ip-pool')
                    ->node('ip_pool', array())
                    ->node('security_group_prefix', 'scalr.')
                ->end()

                ->sub('ui')
                    ->node('support_url', 'https://groups.google.com/d/forum/scalr-discuss')
                    ->node('wiki_url', 'http://wiki.scalr.com')
                ->end()

                ->sub('billing')
                    ->node('enabled')
                    ->node('chargify_api_key', '')
                    ->node('chargify_domain', '')
                    ->node('emergency_phone_number', '')
                ->end()

                ->sub('cloudyn', false)
                    ->node('master_email', '')
                    ->node('environment', 'PROD')
                ->end()

                ->sub('connections')
                    ->sub('ldap', false)
                        ->node('host', 'localhost')
                        ->node('port', null)
                        ->node('base_dn')
                        ->node('user', null)
                        ->node('pass', null)
                    ->end()
                    ->sub('mysql')
                        ->node('driver', 'mysqli')
                        ->node('host', '127.0.0.1')
                        ->node('port', null)
                        ->node('name')
                        ->node('user')
                        ->node('pass')
                    ->end()
                    ->sub('mongodb', false)
                        ->node('driver', 'mongodb')
                        ->node('host', 'localhost')
                        ->node('port', 27017)
                        //name parameter is required only when parent bag is defined
                        ->node('name')
                    ->end()
                ->end()

                ->sub('dns')
                    ->sub('global')
                        ->node('default_domain_name')
                        ->node('enabled')
                        ->node('nameservers')
                    ->end()
                    ->sub('static')
                        ->node('enabled')
                        ->node('nameservers')
                        ->node('domain_name')
                    ->end()
                    ->sub('mysql')
                        ->node('driver', 'mysqli')
                        ->node('host')
                        ->node('port', null)
                        ->node('name')
                        ->node('user')
                        ->node('pass')
                    ->end()
                ->end()

                ->node('environment', 'PROD')

                ->sub('email', false)
                    ->node('address', null)
                    ->node('name', null)
                ->end()

                ->sub('endpoint')
                    ->node('scheme', 'http')
                    ->node('host')
                ->end()

                ->node('instances_connection_policy')

                ->sub('monitoring', false)
                    ->node('server_url', 'http://monitoring.scalr.net')
                ->end()

                ->sub('msg_sender')
                    ->node('pool_size')
                    ->node('log_file')
                    ->node('pid_file')
                    ->sub('connections')
                        ->sub('mysql')
                            ->node('driver')
                            ->node('host')
                            ->node('port', null)
                            ->node('name')
                            ->node('user')
                            ->node('pass')
                            ->node('pool_recycle')
                            ->node('pool_size')
                        ->end()
                    ->end()
                ->end()

                ->sub('phpunit', false)
                    ->node('skip_functional_tests', true)
                    ->node('userid')
                    ->node('envid')
                ->end()

                ->node('pma_instance_ip_address')

                ->node('rss_cache_lifetime', 300)

                ->sub('script', false)
                    ->sub('timeout', false)
                        ->node('sync', 180)
                        ->node('async', 1200)
                    ->end()
                ->end()

                ->sub('stats_poller')
                    ->node('metrics')
                    ->node('farm_procs')
                    ->node('serv_thrds')
                    ->node('rrd_thrds')
                    ->node('rrd_db_dir')
                    ->node('images_path')
                    ->node('graphics_url')
                    ->node('log_file')
                    ->node('pid_file')
                    ->sub('connections')
                        ->sub('mysql')
                            ->node('driver')
                            ->node('host')
                            ->node('port', null)
                            ->node('name')
                            ->node('user')
                            ->node('pass')
                            ->node('pool_recycle')
                            ->node('pool_size')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $this;
    }

    /**
     * Defines subset
     *
     * @param   string    $name     Parameter bag name
     * @param   bool      $required optional Whether this bag is required.
     * @return  Extension
     */
    public function sub($name, $required = true)
    {
        return new ClosureInvoker(function ($method, $invoker) use ($name, $required) {
            $arguments = array_slice(func_get_args(), 2);
            $arguments[0] = $name . '.' . $arguments[0];

            if ($method != 'sub' && strpos($arguments[0], '.')) {
                $p = preg_replace('/\.[^\.]+$/', '', $arguments[0]);
                if ($required) {
                    $obj = new \stdClass();
                    $invoker->getObject()->setParameter($p, $obj);
                }
                $invoker->getObject()->paths[$p] = true;
            }

            $ret = call_user_func_array(array($invoker->getObject(), $method), $arguments);
            if ($ret instanceof ClosureInvoker) {
                $ret->parent = $invoker;
                return $ret;
            } else {
                return $invoker;
            }
        }, $this);
    }

    /**
     * Appends new scalar or scalarArray node to Extension
     *
     * @param   string     $name          Dot notaion name.
     * @param   mixed      $defaultValue  optional Default value for the parameter.
     * @return  Extension
     * @throws  Exception\ExtensionException
     */
    public function node($name, $defaultValue = null)
    {
        if ($name === null || $name == '') {
            throw new Exception\ExtensionException(sprintf(
                'Node name must not be empty.'
            ));
        }

        $obj = new \stdClass();

        if (func_num_args() > 1) {
            if ($defaultValue !== null && !is_scalar($defaultValue)) {
                $valid = true;
                if (!is_array($defaultValue)) {
                    $valid = false;
                } else {
                    //Additional check that all values of arrays are scalar.
                    foreach ($defaultValue as $k => $v) {
                        if ($v !== null && !is_scalar($v)) {
                            $valid = false;
                            break;
                        }
                    }
                }
                if (!$valid) {
                    throw new Exception\ExtensionException(sprintf(
                        'Default node value must be scalar or scalarArray (one dimension array with numeric keys), "%s" given.',
                        gettype($defaultValue)
                    ));
                }
            }
            $obj->default = $defaultValue;
        }

        $this->parameters[$name] = $obj;

        return $this;
    }

    /**
     * {@inheritdoc}
     * @see IteratorAggregate::getIterator()
     */
    public function getIterator()
    {
        $obj = new \ArrayObject($this->parameters);
        return $obj->getIterator();
    }

	/**
     * Checks whether scalar node is defined
     *
     * @param   string   $name  Dot notation key
     * @return  boolean  Returns true if config node is defined
     */
    public function defined($name)
    {
        return array_key_exists($name, $this->parameters);
    }

    public function __invoke($parameter)
    {
        return isset($this->parameters[$parameter]) ? $this->parameters[$parameter] : null;
    }

    /**
     * Sets parameter with specified name
     *
     * @param   string     $name
     * @param   object     $value
     */
    public function setParameter($name, $value)
    {
        $this->parameters[$name] = $value;
    }

    /**
     * Gets parameter with dot notation key
     *
     * @param   string      $name  Dot notaion key
     * @return  object
     */
    public function getParameter($name)
    {
        return isset($this->parameters[$name]) ? $this->parameters[$name] : null;
    }
}
