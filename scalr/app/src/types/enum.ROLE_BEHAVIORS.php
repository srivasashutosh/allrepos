<?php

final class ROLE_BEHAVIORS
{
    const BASE 		= "base";
    const CUSTOM 	= "custom";
    const MYSQL 	= "mysql";
    const MYSQL2 	= "mysql2";
    const PERCONA 	= "percona";
    const NGINX	 	= "www";
    const APACHE 	= "app";
    const TOMCAT    = "tomcat";
    const MEMCACHED = "memcached";
    const CASSANDRA = "cassandra";
    const POSTGRESQL= "postgresql";
    const REDIS		= "redis";
    const RABBITMQ  = "rabbitmq";
    const MONGODB  	= "mongodb";
    const CHEF		= "chef";
    const MYSQLPROXY = "mysqlproxy";
    const HAPROXY	= "haproxy";
    const VPC_ROUTER    = "router";
    const MARIADB    = "mariadb";

    /** CloudFoundry behaviors */
    const CF_ROUTER				= 'cf_router';
    const CF_CLOUD_CONTROLLER 	= 'cf_cloud_controller';
    const CF_HEALTH_MANAGER 	= 'cf_health_manager';
    const CF_DEA 				= 'cf_dea';
    const CF_SERVICE 			= 'cf_service';

    static public function GetCategoryId($const) {

        $behavior = explode(",", $const);

        if (is_array($behavior) && count($behavior) > 1) {
            $cfRole = false;
            foreach ($behavior as $b) {
                if (stristr($b, "cf_")) {
                    $cfRole = true;
                    break;
                }

                if (in_array($b, array(ROLE_BEHAVIORS::CHEF, ROLE_BEHAVIORS::MYSQLPROXY)))
                    continue;

                $rBehavior .= "{$b},";
            }

            if ($cfRole)
                return 7;

            $rBehavior  = trim($rBehavior, ",");
        } else
            $rBehavior = $behavior[0];

        $behaviors = array(
            ROLE_BEHAVIORS::BASE	 => 1,

            ROLE_BEHAVIORS::MYSQL	 => 2,
            ROLE_BEHAVIORS::MYSQL2	 => 2,
            ROLE_BEHAVIORS::PERCONA	 => 2,
            ROLE_BEHAVIORS::POSTGRESQL	 => 2,
            ROLE_BEHAVIORS::REDIS	 => 2,
            ROLE_BEHAVIORS::MONGODB	 => 2,
            ROLE_BEHAVIORS::MARIADB	 => 2,

            ROLE_BEHAVIORS::APACHE	 => 3,
            ROLE_BEHAVIORS::TOMCAT   => 3,

            ROLE_BEHAVIORS::NGINX	 => 4,

            ROLE_BEHAVIORS::MEMCACHED  => 6,


            ROLE_BEHAVIORS::RABBITMQ	 => 5
        );

        if ($behaviors[$rBehavior])
            return $behaviors[$rBehavior];
        else
            return 8;
    }

    static public function GetName($const = null, $all = false)
    {
        $types = array(
            self::BASE	 => _("Base"),
            self::CUSTOM => _("Custom"),
            self::MYSQL	 => _("MySQL (Deprecated)"),
            self::MYSQL2	 => _("MySQL 5"),

            self::PERCONA	 => _("Percona Server 5"),
            self::MARIADB	 => _("MariaDB 5"),

            self::APACHE => _("Apache"),
            self::TOMCAT => _("Tomcat"),
            self::NGINX	 => _("Nginx"),
            self::HAPROXY	 => _("HAProxy"),
            self::MEMCACHED  => _("Memcached"),
            self::CASSANDRA	 => _("Cassandra"),
            self::POSTGRESQL => _("PostgreSQL"),
            self::REDIS 	=> _("Redis"),
            self::RABBITMQ 	=> _("RabbitMQ"),
            self::MONGODB 	=> _("MongoDB"),
            self::CHEF 		=> _("Chef"),
            self::MYSQLPROXY => _("MySQL Proxy"),

            self::VPC_ROUTER => _("VPC Router"),

            self::CF_ROUTER => _("CloudFoundry Router"),
            self::CF_CLOUD_CONTROLLER => _("CloudFoundry Controller"),
            self::CF_HEALTH_MANAGER => _("CloudFoundry Health Manager"),
            self::CF_DEA => _("CloudFoundry DEA"),
            self::CF_SERVICE => _("CloudFoundry Service"),
        );

        return ($all) ? $types : (isset($types[$const]) ? $types[$const] : null);
    }
}
