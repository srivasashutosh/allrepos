

--
-- Table structure for table `account_alerts`
--

CREATE TABLE `account_alerts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) DEFAULT NULL,
  `type` varchar(20) DEFAULT NULL,
  `is_critical` tinyint(1) DEFAULT NULL,
  `is_resolved` tinyint(1) DEFAULT NULL,
  `message` text,
  `env_id` int(11) DEFAULT NULL,
  `dtcreated` datetime DEFAULT NULL,
  `dtresolved` datetime DEFAULT NULL,
  `cloud_location` varchar(50) DEFAULT NULL,
  `platform` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `account_id` (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `account_audit`
--

CREATE TABLE `account_audit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_email` varchar(100) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `action` varchar(45) DEFAULT NULL,
  `ipaddress` varchar(15) DEFAULT NULL,
  `comments` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_account_audit_clients1` (`account_id`),
  KEY `fk_account_audit_account_users1` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `account_groups`
--

CREATE TABLE `account_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `team_id` int(11) NOT NULL,
  `name` varchar(45) DEFAULT NULL,
  `is_active` tinyint(4) DEFAULT '1',
  `color` varchar(16) DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `fk_account_groups_account_teams1` (`team_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `account_group_permissions`
--

CREATE TABLE `account_group_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) DEFAULT NULL,
  `controller` varchar(45) DEFAULT NULL,
  `permissions` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_account_group_permissions_account_groups1` (`group_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `account_limits`
--

CREATE TABLE `account_limits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) DEFAULT NULL,
  `limit_name` varchar(45) DEFAULT NULL,
  `limit_value` int(11) DEFAULT NULL,
  `limit_type` enum('soft','hard') DEFAULT 'hard',
  `limit_type_value` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_account_limits_clients` (`account_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `account_teams`
--

CREATE TABLE `account_teams` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) DEFAULT NULL,
  `name` varchar(45) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_account_teams_clients1` (`account_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `account_team_envs`
--

CREATE TABLE `account_team_envs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `env_id` int(11) DEFAULT NULL,
  `team_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_account_team_envs_account_teams1` (`team_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `account_team_users`
--

CREATE TABLE `account_team_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `team_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `permissions` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_account_team_users_account_teams1` (`team_id`),
  KEY `fk_account_team_users_account_users1` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `account_users`
--

CREATE TABLE `account_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) DEFAULT NULL,
  `status` varchar(45) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `fullname` varchar(100) DEFAULT NULL,
  `password` varchar(64) DEFAULT NULL,
  `dtcreated` datetime DEFAULT NULL,
  `dtlastlogin` datetime DEFAULT NULL,
  `type` varchar(45) DEFAULT NULL,
  `comments` text,
  `loginattempts` int(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_account_users_clients1` (`account_id`),
  KEY `email` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `account_user_dashboard`
--

CREATE TABLE `account_user_dashboard` (
  `user_id` int(11) NOT NULL,
  `env_id` int(11) NOT NULL,
  `value` text NOT NULL,
  UNIQUE KEY `user_id` (`user_id`,`env_id`),
  KEY `env_id` (`env_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `account_user_groups`
--

CREATE TABLE `account_user_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_account_user_groups_account_users1` (`user_id`),
  KEY `fk_account_user_groups_account_groups1` (`group_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `account_user_settings`
--

CREATE TABLE `account_user_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userid_name` (`user_id`,`name`),
  KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `account_user_vars`
--

CREATE TABLE `account_user_vars` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `value` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userid_name` (`user_id`,`name`),
  KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `apache_vhosts`
--

CREATE TABLE `apache_vhosts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `is_ssl_enabled` tinyint(1) DEFAULT '0',
  `farm_id` int(11) DEFAULT NULL,
  `farm_roleid` int(11) DEFAULT NULL,
  `ssl_cert` text,
  `ssl_key` text,
  `ca_cert` text,
  `last_modified` datetime DEFAULT NULL,
  `client_id` int(11) DEFAULT NULL,
  `env_id` int(11) NOT NULL,
  `httpd_conf` text,
  `httpd_conf_vars` text,
  `advanced_mode` tinyint(1) DEFAULT '0',
  `httpd_conf_ssl` text,
  `ssl_cert_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ix_name` (`name`,`env_id`,`farm_id`),
  KEY `clientid` (`client_id`),
  KEY `env_id` (`env_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `api_log`
--

CREATE TABLE `api_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_id` varchar(36) DEFAULT NULL,
  `dtadded` int(11) DEFAULT NULL,
  `action` varchar(25) DEFAULT NULL,
  `ipaddress` varchar(15) DEFAULT NULL,
  `request` text,
  `response` text,
  `clientid` int(11) DEFAULT NULL,
  `env_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `transaction_id` (`transaction_id`),
  KEY `client_index` (`clientid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `autosnap_settings`
--

CREATE TABLE `autosnap_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clientid` int(11) DEFAULT NULL,
  `env_id` int(11) NOT NULL,
  `period` int(5) DEFAULT NULL,
  `dtlastsnapshot` datetime DEFAULT NULL,
  `rotate` int(11) DEFAULT NULL,
  `last_snapshotid` varchar(50) DEFAULT NULL,
  `region` varchar(50) DEFAULT 'us-east-1',
  `objectid` varchar(20) DEFAULT NULL,
  `object_type` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `env_id` (`env_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `aws_errors`
--

CREATE TABLE `aws_errors` (
  `guid` varchar(85) NOT NULL,
  `title` text,
  `pub_date` datetime DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`guid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `aws_regions`
--

CREATE TABLE `aws_regions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `api_url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `billing_packages`
--

CREATE TABLE `billing_packages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `cost` float(7,2) DEFAULT NULL,
  `group` tinyint(2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bundle_tasks`
--

CREATE TABLE `bundle_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prototype_role_id` int(11) DEFAULT NULL,
  `client_id` int(11) DEFAULT NULL,
  `env_id` int(11) NOT NULL,
  `server_id` varchar(36) DEFAULT NULL,
  `replace_type` varchar(20) DEFAULT NULL,
  `status` varchar(30) DEFAULT NULL,
  `platform` varchar(20) DEFAULT NULL,
  `rolename` varchar(50) DEFAULT NULL,
  `failure_reason` text,
  `bundle_type` varchar(20) DEFAULT NULL,
  `dtadded` datetime DEFAULT NULL,
  `dtstarted` datetime DEFAULT NULL,
  `dtfinished` datetime DEFAULT NULL,
  `remove_proto_role` tinyint(1) DEFAULT '0',
  `snapshot_id` varchar(255) DEFAULT NULL,
  `platform_status` varchar(50) DEFAULT NULL,
  `description` text,
  `role_id` int(11) DEFAULT NULL,
  `farm_id` int(11) DEFAULT NULL,
  `cloud_location` varchar(50) DEFAULT NULL,
  `meta_data` text,
  `os_family` varchar(20) DEFAULT NULL,
  `os_name` varchar(255) DEFAULT NULL,
  `os_version` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `clientid` (`client_id`),
  KEY `env_id` (`env_id`),
  KEY `server_id` (`server_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `bundle_task_log`
--

CREATE TABLE `bundle_task_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bundle_task_id` int(11) DEFAULT NULL,
  `dtadded` datetime DEFAULT NULL,
  `message` text,
  PRIMARY KEY (`id`),
  KEY `NewIndex1` (`bundle_task_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `isbilled` tinyint(1) DEFAULT '0',
  `dtdue` datetime DEFAULT NULL,
  `isactive` tinyint(1) DEFAULT '0',
  `fullname` varchar(60) DEFAULT NULL,
  `org` varchar(60) DEFAULT NULL,
  `country` varchar(60) DEFAULT NULL,
  `state` varchar(60) DEFAULT NULL,
  `city` varchar(60) DEFAULT NULL,
  `zipcode` varchar(60) DEFAULT NULL,
  `address1` varchar(60) DEFAULT NULL,
  `address2` varchar(60) DEFAULT NULL,
  `phone` varchar(60) DEFAULT NULL,
  `fax` varchar(60) DEFAULT NULL,
  `dtadded` datetime DEFAULT NULL,
  `iswelcomemailsent` tinyint(1) DEFAULT '0',
  `login_attempts` int(5) DEFAULT '0',
  `dtlastloginattempt` datetime DEFAULT NULL,
  `comments` text,
  `priority` int(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `client_environments`
--

CREATE TABLE `client_environments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `client_id` int(11) NOT NULL,
  `dt_added` datetime NOT NULL,
  `status` varchar(16) NOT NULL DEFAULT 'Active',
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `client_environment_properties`
--

CREATE TABLE `client_environment_properties` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `env_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `group` varchar(20) NOT NULL,
  `cloud` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `env_id_2` (`env_id`,`name`,`group`),
  KEY `env_id` (`env_id`),
  KEY `name_value` (`name`(100),`value`(100)),
  KEY `name` (`name`(100))
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `client_settings`
--

CREATE TABLE `client_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clientid` int(11) DEFAULT NULL,
  `key` varchar(255) DEFAULT NULL,
  `value` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `NewIndex1` (`clientid`,`key`),
  KEY `settingskey` (`key`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `env_id` int(11) NOT NULL,
  `rule` varchar(255) NOT NULL,
  `sg_name` varchar(255) NOT NULL,
  `comment` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `main` (`env_id`,`sg_name`,`rule`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `config`
--

CREATE TABLE `config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) DEFAULT NULL,
  `value` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE `countries` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL DEFAULT '',
  `code` char(2) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `IDX_COUNTRIES_NAME` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `debug_pm`
--

CREATE TABLE `debug_pm` (
  `ip` varchar(16) NOT NULL,
  `cnt` int(11) NOT NULL,
  UNIQUE KEY `ip` (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `debug_rackspace`
--

CREATE TABLE `debug_rackspace` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `server_id` varchar(36) DEFAULT NULL,
  `info` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `debug_scripting`
--

CREATE TABLE `debug_scripting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `request` text,
  `server_id` varchar(36) DEFAULT NULL,
  `params` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `debug_ui`
--

CREATE TABLE `debug_ui` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `path` varchar(255) DEFAULT NULL,
  `time` varchar(50) DEFAULT NULL,
  `ptime` varchar(50) DEFAULT NULL,
  `t1` varchar(10) DEFAULT NULL,
  `t2` varchar(10) DEFAULT NULL,
  `t3` varchar(10) DEFAULT NULL,
  `dtdate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `default_records`
--

CREATE TABLE `default_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clientid` int(11) DEFAULT '0',
  `type` enum('NS','MX','CNAME','A','TXT') DEFAULT NULL,
  `ttl` int(11) DEFAULT '14400',
  `priority` int(11) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `distributions`
--

CREATE TABLE `distributions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cfid` varchar(25) DEFAULT NULL,
  `cfurl` varchar(255) DEFAULT NULL,
  `cname` varchar(255) DEFAULT NULL,
  `zone` varchar(255) DEFAULT NULL,
  `bucket` varchar(255) DEFAULT NULL,
  `clientid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `dm_applications`
--

CREATE TABLE `dm_applications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `env_id` int(11) DEFAULT NULL,
  `dm_source_id` int(11) DEFAULT NULL,
  `pre_deploy_script` text,
  `post_deploy_script` text,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `dm_deployment_tasks`
--

CREATE TABLE `dm_deployment_tasks` (
  `id` varchar(12) NOT NULL,
  `env_id` int(11) DEFAULT NULL,
  `farm_role_id` int(11) DEFAULT NULL,
  `dm_application_id` int(11) DEFAULT NULL,
  `remote_path` varchar(255) DEFAULT NULL,
  `server_id` varchar(36) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `type` varchar(25) DEFAULT NULL,
  `dtdeployed` datetime DEFAULT NULL,
  `dtadded` datetime DEFAULT NULL,
  `last_error` text,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `dm_deployment_task_logs`
--

CREATE TABLE `dm_deployment_task_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dm_deployment_task_id` varchar(12) DEFAULT NULL,
  `dtadded` datetime DEFAULT NULL,
  `message` tinytext,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `dm_sources`
--

CREATE TABLE `dm_sources` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) DEFAULT NULL,
  `url` text,
  `env_id` int(11) DEFAULT NULL,
  `auth_type` enum('password','certificate') DEFAULT NULL,
  `auth_info` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `dns_zones`
--

CREATE TABLE `dns_zones` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` int(11) DEFAULT NULL,
  `env_id` int(11) NOT NULL,
  `farm_id` int(11) DEFAULT NULL,
  `farm_roleid` int(11) DEFAULT NULL,
  `zone_name` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `soa_owner` varchar(100) DEFAULT NULL,
  `soa_ttl` int(10) unsigned DEFAULT NULL,
  `soa_parent` varchar(100) DEFAULT NULL,
  `soa_serial` int(10) unsigned DEFAULT NULL,
  `soa_refresh` int(10) unsigned DEFAULT NULL,
  `soa_retry` int(10) unsigned DEFAULT NULL,
  `soa_expire` int(10) unsigned DEFAULT NULL,
  `soa_min_ttl` int(10) unsigned DEFAULT NULL,
  `dtlastmodified` datetime DEFAULT NULL,
  `axfr_allowed_hosts` tinytext,
  `allow_manage_system_records` tinyint(1) DEFAULT '0',
  `isonnsserver` tinyint(1) DEFAULT '0',
  `iszoneconfigmodified` tinyint(1) DEFAULT '0',
  `allowed_accounts` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `zones_index3945` (`zone_name`),
  KEY `farmid` (`farm_id`),
  KEY `clientid` (`client_id`),
  KEY `env_id` (`env_id`),
  KEY `iszoneconfigmodified` (`iszoneconfigmodified`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `dns_zone_records`
--

CREATE TABLE `dns_zone_records` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `zone_id` int(10) unsigned NOT NULL DEFAULT '0',
  `type` varchar(6) DEFAULT NULL,
  `ttl` int(10) unsigned DEFAULT NULL,
  `priority` int(10) unsigned DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `issystem` tinyint(1) DEFAULT NULL,
  `weight` int(10) DEFAULT NULL,
  `port` int(10) DEFAULT NULL,
  `server_id` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `zoneid` (`zone_id`,`type`(1),`value`,`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `ebs_snaps_info`
--

CREATE TABLE `ebs_snaps_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `snapid` varchar(50) DEFAULT NULL,
  `comment` varchar(255) DEFAULT NULL,
  `dtcreated` datetime DEFAULT NULL,
  `ebs_array_snapid` int(11) DEFAULT '0',
  `region` varchar(255) DEFAULT 'us-east-1',
  `autosnapshotid` int(11) DEFAULT '0',
  `is_autoebs_master_snap` tinyint(1) DEFAULT '0',
  `farm_roleid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mainindex` (`farm_roleid`,`is_autoebs_master_snap`),
  KEY `autosnapid` (`autosnapshotid`),
  KEY `snapid` (`snapid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `ec2_ebs`
--

CREATE TABLE `ec2_ebs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `farm_id` int(11) DEFAULT NULL,
  `farm_roleid` int(11) DEFAULT NULL,
  `volume_id` varchar(15) DEFAULT NULL,
  `server_id` varchar(36) DEFAULT NULL,
  `attachment_status` varchar(30) DEFAULT NULL,
  `mount_status` varchar(20) DEFAULT NULL,
  `device` varchar(15) DEFAULT NULL,
  `server_index` int(3) DEFAULT NULL,
  `mount` tinyint(1) DEFAULT '0',
  `mountpoint` varchar(50) DEFAULT NULL,
  `ec2_avail_zone` varchar(30) DEFAULT NULL,
  `ec2_region` varchar(30) DEFAULT NULL,
  `isfsexist` tinyint(1) DEFAULT '0',
  `ismanual` tinyint(1) DEFAULT '0',
  `size` int(11) DEFAULT NULL,
  `snap_id` varchar(50) DEFAULT NULL,
  `type` enum('standard','io1') NOT NULL DEFAULT 'standard',
  `iops` int(4) DEFAULT NULL,
  `ismysqlvolume` tinyint(1) DEFAULT '0',
  `client_id` int(11) DEFAULT NULL,
  `env_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `env_id` (`env_id`),
  KEY `server_id` (`server_id`),
  KEY `farm_roleid_index` (`farm_roleid`,`server_index`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `elastic_ips`
--

CREATE TABLE `elastic_ips` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `farmid` int(11) DEFAULT NULL,
  `role_name` varchar(100) DEFAULT NULL,
  `ipaddress` varchar(15) DEFAULT NULL,
  `state` tinyint(1) DEFAULT '0',
  `instance_id` varchar(20) DEFAULT NULL,
  `clientid` int(11) DEFAULT NULL,
  `env_id` int(11) NOT NULL,
  `instance_index` int(11) DEFAULT '0',
  `farm_roleid` int(11) DEFAULT NULL,
  `server_id` varchar(36) DEFAULT NULL,
  `allocation_id` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `farmid` (`farmid`),
  KEY `farm_roleid` (`farm_roleid`),
  KEY `env_id` (`env_id`),
  KEY `server_id` (`server_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `farmid` int(11) DEFAULT NULL,
  `type` varchar(25) DEFAULT NULL,
  `dtadded` datetime DEFAULT NULL,
  `message` varchar(255) DEFAULT NULL,
  `ishandled` tinyint(1) DEFAULT '0',
  `short_message` varchar(255) DEFAULT NULL,
  `event_object` text,
  `event_id` varchar(36) DEFAULT NULL,
  `event_server_id` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `event_id` (`event_id`),
  KEY `farmid` (`farmid`),
  KEY `event_server_id` (`event_server_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `event_definitions`
--

CREATE TABLE `event_definitions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `env_id` int(11) NOT NULL,
  `name` varchar(25) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `farms`
--

CREATE TABLE `farms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clientid` int(11) DEFAULT NULL,
  `env_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `iscompleted` tinyint(1) DEFAULT '0',
  `hash` varchar(25) DEFAULT NULL,
  `dtadded` datetime DEFAULT NULL,
  `status` tinyint(1) DEFAULT '1',
  `dtlaunched` datetime DEFAULT NULL,
  `term_on_sync_fail` tinyint(1) DEFAULT '1',
  `region` varchar(255) DEFAULT 'us-east-1',
  `farm_roles_launch_order` tinyint(1) DEFAULT '0',
  `comments` text,
  `created_by_id` int(11) DEFAULT NULL,
  `created_by_email` varchar(250) DEFAULT NULL,
  `changed_by_id` int(11) NOT NULL,
  `changed_time` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `clientid` (`clientid`),
  KEY `env_id` (`env_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `farm_event_observers`
--

CREATE TABLE `farm_event_observers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `farmid` int(11) DEFAULT NULL,
  `event_observer_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `NewIndex1` (`farmid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `farm_event_observers_config`
--

CREATE TABLE `farm_event_observers_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `observerid` int(11) DEFAULT NULL,
  `key` varchar(255) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `NewIndex1` (`observerid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `farm_roles`
--

CREATE TABLE `farm_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `farmid` int(11) DEFAULT NULL,
  `dtlastsync` datetime DEFAULT NULL,
  `reboot_timeout` int(10) DEFAULT '300',
  `launch_timeout` int(10) DEFAULT '300',
  `status_timeout` int(10) DEFAULT '20',
  `launch_index` int(5) DEFAULT '0',
  `role_id` int(11) DEFAULT NULL,
  `new_role_id` int(11) DEFAULT NULL,
  `platform` varchar(20) DEFAULT NULL,
  `cloud_location` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `role_id` (`role_id`),
  KEY `farmid` (`farmid`),
  KEY `platform` (`platform`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `farm_role_cloud_services`
--

CREATE TABLE `farm_role_cloud_services` (
  `id` varchar(36) NOT NULL,
  `type` varchar(10) NOT NULL,
  `env_id` int(11) NOT NULL,
  `farm_id` int(11) NOT NULL,
  `farm_role_id` int(11) NOT NULL,
  `platform` varchar(36) DEFAULT NULL,
  `cloud_location` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `farm_role_id` (`farm_role_id`),
  KEY `farm_id` (`farm_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `farm_role_config_presets`
--

CREATE TABLE `farm_role_config_presets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `farm_roleid` int(11) DEFAULT NULL,
  `behavior` varchar(25) DEFAULT NULL,
  `cfg_filename` varchar(25) DEFAULT NULL,
  `cfg_key` varchar(100) DEFAULT NULL,
  `cfg_value` text,
  PRIMARY KEY (`id`),
  KEY `main` (`farm_roleid`,`behavior`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `farm_role_options`
--

CREATE TABLE `farm_role_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `farmid` int(11) DEFAULT NULL,
  `ami_id` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `value` text,
  `hash` varchar(255) DEFAULT NULL,
  `farm_roleid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `farmid` (`farmid`),
  KEY `farm_roleid` (`farm_roleid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `farm_role_scaling_metrics`
--

CREATE TABLE `farm_role_scaling_metrics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `farm_roleid` int(11) DEFAULT NULL,
  `metric_id` int(11) DEFAULT NULL,
  `dtlastpolled` datetime DEFAULT NULL,
  `last_value` varchar(255) DEFAULT NULL,
  `settings` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `NewIndex4` (`farm_roleid`,`metric_id`),
  KEY `NewIndex1` (`farm_roleid`),
  KEY `NewIndex2` (`metric_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `farm_role_scaling_times`
--

CREATE TABLE `farm_role_scaling_times` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `farm_roleid` int(11) DEFAULT NULL,
  `start_time` int(11) DEFAULT NULL,
  `end_time` int(11) DEFAULT NULL,
  `days_of_week` varchar(75) DEFAULT NULL,
  `instances_count` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `farmroleid` (`farm_roleid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `farm_role_scripting_params`
--

CREATE TABLE `farm_role_scripting_params` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `farm_role_id` int(11) DEFAULT NULL,
  `role_script_id` int(11) DEFAULT NULL,
  `farm_role_script_id` int(11) DEFAULT NULL,
  `hash` varchar(12) DEFAULT NULL,
  `params` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq` (`farm_role_id`,`hash`,`farm_role_script_id`),
  KEY `farm_roleid` (`farm_role_id`),
  KEY `role_script_id` (`role_script_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `farm_role_scripting_targets`
--

CREATE TABLE `farm_role_scripting_targets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `farm_role_script_id` int(11) DEFAULT NULL,
  `target_type` enum('farmrole','behavior') DEFAULT NULL,
  `target` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `farm_role_script_id` (`farm_role_script_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `farm_role_scripts`
--

CREATE TABLE `farm_role_scripts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `scriptid` int(11) DEFAULT NULL,
  `farmid` int(11) DEFAULT NULL,
  `ami_id` varchar(255) DEFAULT NULL,
  `params` text,
  `event_name` varchar(255) DEFAULT NULL,
  `target` varchar(50) DEFAULT NULL,
  `version` varchar(20) DEFAULT 'latest',
  `timeout` int(5) DEFAULT '120',
  `issync` tinyint(1) DEFAULT '0',
  `ismenuitem` tinyint(1) DEFAULT '0',
  `order_index` int(5) DEFAULT '0',
  `farm_roleid` int(11) DEFAULT NULL,
  `issystem` tinyint(1) DEFAULT '0',
  `debug` varchar(50) DEFAULT NULL,
  `script_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `farmid` (`farmid`),
  KEY `farm_roleid` (`farm_roleid`),
  KEY `event_name` (`event_name`),
  KEY `UniqueIndex` (`scriptid`,`farmid`,`event_name`,`farm_roleid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `farm_role_service_config_presets`
--

CREATE TABLE `farm_role_service_config_presets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `preset_id` int(11) NOT NULL,
  `farm_roleid` int(11) DEFAULT NULL,
  `behavior` varchar(25) DEFAULT NULL,
  `restart_service` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `fk_farm_role_service_config_presets_service_config_presets1` (`preset_id`),
  KEY `farm_roleid` (`farm_roleid`),
  KEY `preset_id` (`preset_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `farm_role_settings`
--

CREATE TABLE `farm_role_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `farm_roleid` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `value` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique` (`farm_roleid`,`name`),
  KEY `name` (`name`(30))
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Table structure for table `farm_role_storage_config`
--

CREATE TABLE `farm_role_storage_config` (
  `id` varchar(36) NOT NULL,
  `farm_role_id` int(11) DEFAULT NULL,
  `index` tinyint(3) DEFAULT NULL,
  `type` varchar(15) DEFAULT NULL,
  `fs` varchar(15) DEFAULT NULL,
  `re_use` tinyint(1) DEFAULT NULL,
  `rebuild` tinyint(1) DEFAULT '0',
  `mount` tinyint(1) DEFAULT NULL,
  `mountpoint` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  UNIQUE KEY `id` (`id`),
  KEY `farm_role_id` (`farm_role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `farm_role_storage_devices`
--

CREATE TABLE `farm_role_storage_devices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `farm_role_id` int(11) DEFAULT NULL,
  `env_id` int(11) DEFAULT NULL,
  `cloud_location` varchar(50) DEFAULT NULL,
  `server_index` tinyint(4) DEFAULT NULL,
  `placement` varchar(36) DEFAULT NULL,
  `storage_config_id` varchar(36) DEFAULT NULL,
  `config` text,
  `storage_id` varchar(36) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `storage_id` (`storage_id`),
  KEY `storage_config_id` (`storage_config_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `farm_role_storage_settings`
--

CREATE TABLE `farm_role_storage_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `storage_config_id` varchar(36) DEFAULT NULL,
  `name` varchar(45) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `storage_config` (`storage_config_id`,`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `farm_settings`
--

CREATE TABLE `farm_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `farmid` int(11) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `value` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `farmid_name` (`farmid`,`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `farm_stats`
--

CREATE TABLE `farm_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `farmid` int(11) DEFAULT NULL,
  `bw_in` bigint(20) DEFAULT '0',
  `bw_out` bigint(20) DEFAULT '0',
  `bw_in_last` int(11) DEFAULT '0',
  `bw_out_last` int(11) DEFAULT '0',
  `month` int(2) DEFAULT NULL,
  `year` int(4) DEFAULT NULL,
  `dtlastupdate` int(11) DEFAULT NULL,
  `m1_small` int(11) DEFAULT '0',
  `m1_large` int(11) DEFAULT '0',
  `m1_xlarge` int(11) DEFAULT '0',
  `c1_medium` int(11) DEFAULT '0',
  `c1_xlarge` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `NewIndex1` (`month`,`year`),
  KEY `NewIndex2` (`farmid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `garbage_queue`
--

CREATE TABLE `garbage_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clientid` int(11) DEFAULT NULL,
  `data` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `NewIndex1` (`clientid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `global_variables`
--

CREATE TABLE `global_variables` (
  `env_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `farm_id` int(11) NOT NULL,
  `farm_role_id` int(11) NOT NULL,
  `name` varchar(30) NOT NULL,
  `value` text,
  `flag_final` tinyint(1) DEFAULT '0',
  `flag_required` tinyint(1) DEFAULT '0',
  `scope` enum('env','role','farm','farmrole') DEFAULT NULL,
  PRIMARY KEY (`env_id`,`role_id`,`farm_id`,`farm_role_id`,`name`),
  KEY `name` (`name`),
  KEY `role_id` (`role_id`),
  KEY `farm_id` (`farm_id`),
  KEY `farm_role_id` (`farm_role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `global_variables_backup`
--

CREATE TABLE `global_variables_backup` (
  `env_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `farm_id` int(11) NOT NULL,
  `farm_role_id` int(11) NOT NULL,
  `name` varchar(30) NOT NULL,
  `value` text,
  `flag_final` tinyint(1) DEFAULT '0',
  `flag_required` tinyint(1) DEFAULT '0',
  `scope` enum('env','role','farm','farmrole') DEFAULT NULL,
  PRIMARY KEY (`env_id`,`role_id`,`farm_id`,`farm_role_id`,`name`),
  KEY `name` (`name`),
  KEY `role_id` (`role_id`),
  KEY `farm_id` (`farm_id`),
  KEY `farm_role_id` (`farm_role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `init_tokens`
--

CREATE TABLE `init_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` varchar(255) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `dtadded` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `instances_history`
--

CREATE TABLE `instances_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` varchar(20) DEFAULT NULL,
  `dtlaunched` int(11) DEFAULT NULL,
  `dtterminated` int(11) DEFAULT NULL,
  `uptime` int(11) DEFAULT NULL,
  `instance_type` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ipaccess`
--

CREATE TABLE `ipaccess` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ipaddress` varchar(255) DEFAULT NULL,
  `comment` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `logentries`
--

CREATE TABLE `logentries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `serverid` varchar(36) NOT NULL,
  `message` text NOT NULL,
  `severity` tinyint(1) DEFAULT '0',
  `time` int(11) NOT NULL,
  `source` varchar(255) DEFAULT NULL,
  `farmid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `NewIndex1` (`farmid`),
  KEY `NewIndex2` (`severity`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `messageid` varchar(75) DEFAULT NULL,
  `instance_id` varchar(15) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '0',
  `handle_attempts` int(2) DEFAULT '1',
  `dtlasthandleattempt` datetime DEFAULT NULL,
  `message` longtext,
  `json_message` text,
  `server_id` varchar(36) DEFAULT NULL,
  `type` enum('in','out') DEFAULT NULL,
  `isszr` tinyint(1) DEFAULT '0',
  `message_name` varchar(30) DEFAULT NULL,
  `message_version` int(2) DEFAULT NULL,
  `ipaddress` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `server_message` (`messageid`(36),`server_id`),
  KEY `server_id` (`server_id`),
  KEY `serverid_isszr` (`server_id`,`isszr`),
  KEY `messageid` (`messageid`),
  KEY `status` (`status`,`type`),
  KEY `message_name` (`message_name`),
  KEY `dt` (`dtlasthandleattempt`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `nameservers`
--

CREATE TABLE `nameservers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `host` varchar(100) DEFAULT NULL,
  `port` int(10) unsigned DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password` text,
  `rndc_path` varchar(255) DEFAULT NULL,
  `named_path` varchar(255) DEFAULT NULL,
  `namedconf_path` varchar(255) DEFAULT NULL,
  `isproxy` tinyint(1) DEFAULT '0',
  `isbackup` tinyint(1) DEFAULT '0',
  `ipaddress` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;


--
-- Table structure for table `rds_snaps_info`
--

CREATE TABLE `rds_snaps_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `snapid` varchar(50) DEFAULT NULL,
  `comment` varchar(255) DEFAULT NULL,
  `dtcreated` datetime DEFAULT NULL,
  `region` varchar(255) DEFAULT 'us-east-1',
  `autosnapshotid` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `real_servers`
--

CREATE TABLE `real_servers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `farmid` int(11) DEFAULT NULL,
  `ami_id` varchar(255) DEFAULT NULL,
  `ipaddress` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `rebundle_log`
--

CREATE TABLE `rebundle_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roleid` int(11) DEFAULT NULL,
  `dtadded` datetime DEFAULT NULL,
  `message` text,
  `bundle_task_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `records`
--

CREATE TABLE `records` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `zoneid` int(10) unsigned NOT NULL DEFAULT '0',
  `rtype` varchar(6) DEFAULT NULL,
  `ttl` int(10) unsigned DEFAULT NULL,
  `rpriority` int(10) unsigned DEFAULT NULL,
  `rvalue` varchar(255) DEFAULT NULL,
  `rkey` varchar(255) DEFAULT NULL,
  `issystem` tinyint(1) DEFAULT NULL,
  `rweight` int(10) DEFAULT NULL,
  `rport` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `zoneid` (`zoneid`,`rtype`(1),`rvalue`,`rkey`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `origin` enum('SHARED','CUSTOM') DEFAULT NULL,
  `client_id` int(11) DEFAULT NULL,
  `env_id` int(11) DEFAULT NULL,
  `cat_id` int(11) DEFAULT NULL,
  `description` text,
  `behaviors` varchar(90) DEFAULT NULL,
  `is_devel` tinyint(1) NOT NULL DEFAULT '0',
  `history` text,
  `generation` tinyint(4) DEFAULT '1',
  `os` varchar(60) DEFAULT NULL,
  `os_family` varchar(30) DEFAULT NULL,
  `os_generation` varchar(10) DEFAULT NULL,
  `os_version` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `NewIndex1` (`origin`),
  KEY `NewIndex2` (`client_id`),
  KEY `NewIndex3` (`env_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `roles_queue`
--

CREATE TABLE `roles_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) DEFAULT NULL,
  `dtadded` datetime DEFAULT NULL,
  `action` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `role_id` (`role_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `role_behaviors`
--

CREATE TABLE `role_behaviors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) DEFAULT NULL,
  `behavior` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_id_behavior` (`role_id`,`behavior`),
  KEY `role_id` (`role_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `role_categories`
--

CREATE TABLE `role_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `env_id` int(11) NOT NULL,
  `name` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `role_images`
--

CREATE TABLE `role_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `cloud_location` varchar(36) DEFAULT NULL,
  `image_id` varchar(255) DEFAULT NULL,
  `platform` varchar(25) DEFAULT NULL,
  `architecture` varchar(6) DEFAULT NULL,
  `os_family` varchar(25) DEFAULT NULL,
  `os_name` varchar(50) DEFAULT NULL,
  `os_version` varchar(10) DEFAULT NULL,
  `agent_version` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique` (`role_id`,`image_id`,`cloud_location`),
  UNIQUE KEY `role_id_location` (`role_id`,`cloud_location`),
  KEY `NewIndex1` (`platform`),
  KEY `location` (`cloud_location`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `role_parameters`
--

CREATE TABLE `role_parameters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `name` varchar(45) DEFAULT NULL,
  `type` varchar(45) DEFAULT NULL,
  `isrequired` tinyint(1) DEFAULT NULL,
  `defval` text,
  `allow_multiple_choice` tinyint(1) DEFAULT NULL,
  `options` text,
  `hash` varchar(45) DEFAULT NULL,
  `issystem` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `role_id` (`role_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `role_properties`
--

CREATE TABLE `role_properties` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `value` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `NewIndex1` (`role_id`,`name`),
  KEY `role_id` (`role_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `role_scripts`
--

CREATE TABLE `role_scripts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) DEFAULT NULL,
  `event_name` varchar(50) DEFAULT NULL,
  `target` varchar(15) DEFAULT NULL,
  `script_id` int(11) DEFAULT NULL,
  `version` varchar(10) DEFAULT NULL,
  `timeout` int(5) DEFAULT NULL,
  `issync` tinyint(1) DEFAULT NULL,
  `params` text,
  `order_index` int(11) NOT NULL DEFAULT '0',
  `hash` varchar(12) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `role_id` (`role_id`),
  KEY `script_id` (`script_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `role_security_rules`
--

CREATE TABLE `role_security_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `rule` varchar(90) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `role_id` (`role_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `role_software`
--

CREATE TABLE `role_software` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `software_name` varchar(45) DEFAULT NULL,
  `software_version` varchar(20) DEFAULT NULL,
  `software_key` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `role_id` (`role_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `role_tags`
--

CREATE TABLE `role_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) DEFAULT NULL,
  `tag` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_tag` (`role_id`,`tag`),
  KEY `role_id` (`role_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `scaling_metrics`
--

CREATE TABLE `scaling_metrics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) DEFAULT NULL,
  `env_id` int(11) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `retrieve_method` varchar(20) DEFAULT NULL,
  `calc_function` varchar(20) DEFAULT NULL,
  `algorithm` varchar(15) DEFAULT NULL,
  `alias` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `NewIndex3` (`client_id`,`name`),
  KEY `NewIndex1` (`client_id`),
  KEY `NewIndex2` (`env_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `scheduler`
--

CREATE TABLE `scheduler` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `type` enum('script_exec','terminate_farm','launch_farm') DEFAULT NULL,
  `comments` varchar(255) DEFAULT NULL,
  `target_id` int(11) DEFAULT NULL COMMENT 'id of farm, farm_role from other tables',
  `target_server_index` int(11) DEFAULT NULL,
  `target_type` enum('farm','role','instance') DEFAULT NULL,
  `start_time` datetime DEFAULT NULL COMMENT 'start task''s time',
  `end_time` datetime DEFAULT NULL COMMENT 'end task by this time',
  `last_start_time` datetime DEFAULT NULL COMMENT 'the last time task was started',
  `restart_every` int(11) DEFAULT '0' COMMENT 'restart task every N minutes',
  `config` text COMMENT 'arguments for action',
  `order_index` int(11) DEFAULT NULL COMMENT 'task order',
  `timezone` varchar(100) DEFAULT NULL,
  `status` varchar(11) DEFAULT NULL COMMENT 'active, suspended, finished',
  `account_id` int(11) DEFAULT NULL COMMENT 'Task belongs to selected account',
  `env_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `account_id` (`account_id`,`env_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `scripting_log`
--

CREATE TABLE `scripting_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `farmid` int(11) DEFAULT NULL,
  `event` varchar(255) DEFAULT NULL,
  `server_id` varchar(36) DEFAULT NULL,
  `dtadded` datetime DEFAULT NULL,
  `message` text,
  `event_server_id` varchar(36) DEFAULT NULL,
  `script_name` varchar(50) DEFAULT NULL,
  `exec_time` int(11) DEFAULT NULL,
  `exec_exitcode` int(11) DEFAULT NULL,
  `event_id` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `farmid` (`farmid`),
  KEY `server_id` (`server_id`),
  KEY `event_id` (`event_id`),
  KEY `event_server_id` (`event_server_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `scripts`
--

CREATE TABLE `scripts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `origin` varchar(50) DEFAULT NULL,
  `dtadded` datetime DEFAULT NULL,
  `issync` tinyint(1) DEFAULT '0',
  `clientid` int(11) DEFAULT '0',
  `approval_state` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `script_revisions`
--

CREATE TABLE `script_revisions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `scriptid` int(11) DEFAULT NULL,
  `revision` int(11) DEFAULT NULL,
  `script` longtext,
  `dtcreated` datetime DEFAULT NULL,
  `approval_state` varchar(255) DEFAULT NULL,
  `variables` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `scriptid_revision` (`scriptid`,`revision`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `sensor_data`
--

CREATE TABLE `sensor_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `farm_roleid` int(11) DEFAULT NULL,
  `sensor_name` varchar(255) DEFAULT NULL,
  `sensor_value` varchar(255) DEFAULT NULL,
  `dtlastupdate` int(11) DEFAULT NULL,
  `raw_sensor_data` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique` (`farm_roleid`,`sensor_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `servers`
--

CREATE TABLE `servers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `server_id` varchar(36) DEFAULT NULL,
  `farm_id` int(11) DEFAULT NULL,
  `farm_roleid` int(11) DEFAULT NULL,
  `client_id` int(11) DEFAULT NULL,
  `env_id` int(11) NOT NULL,
  `role_id` int(11) DEFAULT NULL,
  `platform` varchar(20) DEFAULT NULL,
  `status` varchar(25) DEFAULT NULL,
  `remote_ip` varchar(15) DEFAULT NULL,
  `local_ip` varchar(15) DEFAULT NULL,
  `dtadded` datetime DEFAULT NULL,
  `index` int(11) DEFAULT NULL,
  `dtshutdownscheduled` datetime DEFAULT NULL,
  `dtrebootstart` datetime DEFAULT NULL,
  `replace_server_id` varchar(36) DEFAULT NULL,
  `dtlastsync` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `serverid` (`server_id`),
  KEY `farm_roleid` (`farm_roleid`),
  KEY `farmid_status` (`farm_id`,`status`),
  KEY `local_ip` (`local_ip`),
  KEY `env_id` (`env_id`),
  KEY `role_id` (`role_id`),
  KEY `client_id` (`client_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `servers_history`
--

CREATE TABLE `servers_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) DEFAULT NULL,
  `server_id` varchar(36) DEFAULT NULL,
  `cloud_server_id` varchar(50) DEFAULT NULL,
  `dtlaunched` datetime DEFAULT NULL,
  `dtterminated` datetime DEFAULT NULL,
  `dtterminated_scalr` datetime DEFAULT NULL,
  `launch_reason` varchar(255) DEFAULT NULL,
  `terminate_reason` varchar(255) DEFAULT NULL,
  `platform` varchar(20) DEFAULT NULL,
  `type` varchar(25) DEFAULT NULL,
  `env_id` int(11) DEFAULT NULL,
  `farm_id` int(11) DEFAULT NULL,
  `farm_roleid` int(11) DEFAULT NULL,
  `server_index` int(5) DEFAULT NULL,
  `shutdown_confirmed` tinyint(3) NOT NULL DEFAULT '0',
  `scu_used` float(11,2) DEFAULT '0.00',
  `scu_reported` float(11,2) DEFAULT '0.00',
  `scu_updated` tinyint(1) DEFAULT '0',
  `scu_collecting` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  KEY `server_id` (`server_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `servers_stats`
--

CREATE TABLE `servers_stats` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `usage` int(11) DEFAULT NULL,
  `instance_type` varchar(15) DEFAULT NULL,
  `env_id` int(11) DEFAULT NULL,
  `month` int(2) DEFAULT NULL,
  `year` int(4) DEFAULT NULL,
  `farm_id` int(11) DEFAULT NULL,
  `cloud_location` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `main` (`instance_type`,`cloud_location`,`farm_id`,`env_id`,`month`,`year`),
  KEY `envid` (`env_id`),
  KEY `farm_id` (`farm_id`),
  KEY `year` (`year`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `server_alerts`
--

CREATE TABLE `server_alerts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `env_id` int(11) DEFAULT NULL,
  `farm_id` int(11) DEFAULT NULL,
  `farm_roleid` int(11) DEFAULT NULL,
  `server_index` int(11) DEFAULT NULL,
  `server_id` varchar(36) DEFAULT NULL,
  `metric` varchar(20) DEFAULT NULL,
  `dtoccured` datetime DEFAULT NULL,
  `dtlastcheck` datetime DEFAULT NULL,
  `dtsolved` datetime DEFAULT NULL,
  `details` varchar(255) DEFAULT NULL,
  `status` enum('resolved','failed') DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `main2` (`server_id`,`metric`,`status`),
  KEY `env_id` (`env_id`),
  KEY `farm_role` (`farm_id`,`farm_roleid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `server_operations`
--

CREATE TABLE `server_operations` (
  `id` varchar(36) NOT NULL DEFAULT '',
  `timestamp` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `server_id` varchar(36) NOT NULL DEFAULT '',
  `name` varchar(50) DEFAULT NULL,
  `phases` text,
  UNIQUE KEY `id` (`id`),
  KEY `server_id` (`server_id`,`name`(20))
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `server_operation_progress`
--

CREATE TABLE `server_operation_progress` (
  `operation_id` varchar(36) NOT NULL,
  `timestamp` int(11) DEFAULT NULL,
  `phase` varchar(100) NOT NULL,
  `step` varchar(100) NOT NULL,
  `status` varchar(15) NOT NULL,
  `progress` int(11) DEFAULT NULL,
  `stepno` int(11) DEFAULT NULL,
  `message` text,
  `trace` text,
  `handler` varchar(255) DEFAULT NULL,
  UNIQUE KEY `unique` (`operation_id`,`phase`,`step`),
  KEY `operation_id` (`operation_id`),
  KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `server_properties`
--

CREATE TABLE `server_properties` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `server_id` varchar(36) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `value` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `serverid_name` (`server_id`,`name`),
  KEY `serverid` (`server_id`),
  KEY `name_value` (`name`(20),`value`(20))
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `services_chef_runlists`
--

CREATE TABLE `services_chef_runlists` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `env_id` int(11) DEFAULT NULL,
  `chef_server_id` int(11) DEFAULT NULL,
  `name` varchar(30) NOT NULL,
  `description` varchar(255) NOT NULL,
  `runlist` text,
  `attributes` text,
  `chef_environment` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `services_chef_servers`
--

CREATE TABLE `services_chef_servers` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `env_id` int(11) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `auth_key` text,
  `v_username` varchar(255) DEFAULT NULL,
  `v_auth_key` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `services_db_backups`
--

CREATE TABLE `services_db_backups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` varchar(25) DEFAULT NULL,
  `env_id` int(11) DEFAULT NULL,
  `farm_id` int(11) DEFAULT NULL,
  `service` varchar(50) DEFAULT NULL,
  `platform` varchar(25) DEFAULT NULL,
  `provider` varchar(20) DEFAULT NULL,
  `dtcreated` datetime DEFAULT NULL,
  `size` bigint(20) DEFAULT NULL,
  `cloud_location` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `env_id` (`env_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `services_db_backups_history`
--

CREATE TABLE `services_db_backups_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `farm_role_id` int(11) NOT NULL,
  `operation` enum('backup','bundle') NOT NULL,
  `date` datetime NOT NULL,
  `status` enum('ok','error') NOT NULL,
  `error` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `main` (`farm_role_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `services_db_backup_parts`
--

CREATE TABLE `services_db_backup_parts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `backup_id` int(11) DEFAULT NULL,
  `path` text,
  `size` int(11) DEFAULT NULL,
  `seq_number` int(5) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `backup_id` (`backup_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `services_mongodb_cluster_log`
--

CREATE TABLE `services_mongodb_cluster_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `farm_roleid` int(11) DEFAULT NULL,
  `severity` enum('INFO','WARNING','ERROR') DEFAULT NULL,
  `dtadded` datetime DEFAULT NULL,
  `message` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `services_mongodb_config_servers`
--

CREATE TABLE `services_mongodb_config_servers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `farm_role_id` int(11) NOT NULL,
  `config_server_index` tinyint(1) NOT NULL,
  `shard_index` tinyint(2) NOT NULL,
  `replica_set_index` tinyint(2) NOT NULL,
  `volume_id` varchar(36) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `farm_roleid_index` (`farm_role_id`,`config_server_index`),
  KEY `farm_role_id` (`farm_role_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `services_mongodb_snapshots_map`
--

CREATE TABLE `services_mongodb_snapshots_map` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `farm_roleid` int(11) NOT NULL,
  `shard_index` int(11) NOT NULL,
  `snapshot_id` varchar(25) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `main` (`farm_roleid`,`shard_index`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `services_mongodb_volumes_map`
--

CREATE TABLE `services_mongodb_volumes_map` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `farm_roleid` int(11) NOT NULL,
  `replica_set_index` int(11) NOT NULL,
  `shard_index` int(11) NOT NULL,
  `volume_id` varchar(36) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `main` (`farm_roleid`,`replica_set_index`,`shard_index`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `services_ssl_certs`
--

CREATE TABLE `services_ssl_certs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `env_id` int(11) NOT NULL,
  `name` varchar(40) NOT NULL,
  `ssl_pkey` text,
  `ssl_cert` text,
  `ssl_cabundle` text,
  `ssl_pkey_password` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `service_config_presets`
--

CREATE TABLE `service_config_presets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `env_id` int(11) DEFAULT NULL,
  `client_id` int(11) DEFAULT NULL,
  `name` varchar(45) DEFAULT NULL,
  `role_behavior` varchar(20) DEFAULT NULL,
  `dtadded` datetime DEFAULT NULL,
  `dtlastmodified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `env_id` (`env_id`),
  KEY `client_id` (`client_id`),
  KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `service_config_preset_data`
--

CREATE TABLE `service_config_preset_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `preset_id` int(11) NOT NULL,
  `key` varchar(45) DEFAULT NULL,
  `value` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ssh_keys`
--

CREATE TABLE `ssh_keys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) DEFAULT NULL,
  `env_id` int(11) DEFAULT NULL,
  `type` varchar(10) DEFAULT NULL,
  `private_key` text,
  `public_key` text,
  `cloud_location` varchar(255) DEFAULT NULL,
  `farm_id` int(11) DEFAULT NULL,
  `cloud_key_name` varchar(255) DEFAULT NULL,
  `platform` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `farmid` (`farm_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `storage_backup_configs`
--

CREATE TABLE `storage_backup_configs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(25) DEFAULT NULL,
  `backup_type` varchar(25) DEFAULT NULL,
  `volume_config` text,
  `farm_roleid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `farm_roleid` (`farm_roleid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `storage_restore_configs`
--

CREATE TABLE `storage_restore_configs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `farm_roleid` int(11) DEFAULT NULL,
  `dtadded` datetime DEFAULT NULL,
  `manifest` text,
  `type` enum('full','incremental') NOT NULL,
  `parent_manifest` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `storage_snapshots`
--

CREATE TABLE `storage_snapshots` (
  `id` varchar(36) NOT NULL,
  `client_id` int(11) DEFAULT NULL,
  `env_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `platform` varchar(50) DEFAULT NULL,
  `type` varchar(20) DEFAULT NULL,
  `config` text,
  `description` text,
  `ismysql` tinyint(1) DEFAULT '0',
  `dtcreated` datetime DEFAULT NULL,
  `farm_id` int(11) DEFAULT NULL,
  `farm_roleid` int(11) DEFAULT NULL,
  `service` varchar(50) DEFAULT NULL,
  `cloud_location` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `farm_roleid` (`farm_roleid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `storage_volumes`
--

CREATE TABLE `storage_volumes` (
  `id` varchar(50) NOT NULL,
  `client_id` int(11) DEFAULT NULL,
  `env_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `attachment_status` varchar(255) DEFAULT NULL,
  `mount_status` varchar(255) DEFAULT NULL,
  `config` text,
  `type` varchar(20) DEFAULT NULL,
  `dtcreated` datetime DEFAULT NULL,
  `platform` varchar(20) DEFAULT NULL,
  `size` varchar(20) DEFAULT NULL,
  `fstype` varchar(255) DEFAULT NULL,
  `farm_roleid` int(11) DEFAULT NULL,
  `server_index` int(11) DEFAULT NULL,
  `purpose` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------


--
-- Table structure for table `syslog`
--

CREATE TABLE `syslog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `dtadded` datetime DEFAULT NULL,
  `message` text,
  `severity` varchar(10) DEFAULT NULL,
  `dtadded_time` bigint(20) DEFAULT NULL,
  `transactionid` varchar(50) DEFAULT NULL,
  `backtrace` text,
  `caller` varchar(255) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `sub_transactionid` varchar(50) DEFAULT NULL,
  `farmid` varchar(20) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `NewIndex1` (`transactionid`),
  KEY `NewIndex2` (`sub_transactionid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


-- --------------------------------------------------------

--
-- Table structure for table `syslog_metadata`
--

CREATE TABLE `syslog_metadata` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transactionid` varchar(50) DEFAULT NULL,
  `errors` int(5) DEFAULT NULL,
  `warnings` int(5) DEFAULT NULL,
  `message` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `transid` (`transactionid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `task_queue`
--

CREATE TABLE `task_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `queue_name` varchar(255) DEFAULT NULL,
  `data` text,
  `dtadded` datetime DEFAULT NULL,
  `failed_attempts` int(3) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ui_debug_log`
--

CREATE TABLE `ui_debug_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ipaddress` varchar(15) DEFAULT NULL,
  `dtadded` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `url` varchar(255) DEFAULT NULL,
  `report` text,
  `env_id` int(11) DEFAULT NULL,
  `account_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ui_errors`
--

CREATE TABLE `ui_errors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tm` datetime NOT NULL,
  `file` varchar(255) NOT NULL,
  `lineno` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `short` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `browser` varchar(255) NOT NULL,
  `cnt` int(11) NOT NULL DEFAULT '1',
  `account_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `info` (`file`,`lineno`,`short`,`account_id`,`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


--
-- Constraints for dumped tables
--

--
-- Constraints for table `account_alerts`
--
ALTER TABLE `account_alerts`
  ADD CONSTRAINT `account_alerts_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `account_user_dashboard`
--
ALTER TABLE `account_user_dashboard`
  ADD CONSTRAINT `account_user_dashboard_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `account_users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `account_user_dashboard_ibfk_2` FOREIGN KEY (`env_id`) REFERENCES `client_environments` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `account_user_vars`
--
ALTER TABLE `account_user_vars`
  ADD CONSTRAINT `account_user_vars_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `account_users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `autosnap_settings`
--
ALTER TABLE `autosnap_settings`
  ADD CONSTRAINT `autosnap_settings_ibfk_1` FOREIGN KEY (`env_id`) REFERENCES `client_environments` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `bundle_tasks`
--
ALTER TABLE `bundle_tasks`
  ADD CONSTRAINT `bundle_tasks_ibfk_1` FOREIGN KEY (`env_id`) REFERENCES `client_environments` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `farms`
--
ALTER TABLE `farms`
  ADD CONSTRAINT `farms_ibfk_1` FOREIGN KEY (`clientid`) REFERENCES `clients` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `farm_roles`
--
ALTER TABLE `farm_roles`
  ADD CONSTRAINT `farm_roles_ibfk_1` FOREIGN KEY (`farmid`) REFERENCES `farms` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `farm_role_cloud_services`
--
ALTER TABLE `farm_role_cloud_services`
  ADD CONSTRAINT `farm_role_cloud_services_ibfk_1` FOREIGN KEY (`farm_role_id`) REFERENCES `farm_roles` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `farm_role_config_presets`
--
ALTER TABLE `farm_role_config_presets`
  ADD CONSTRAINT `farm_role_config_presets_ibfk_1` FOREIGN KEY (`farm_roleid`) REFERENCES `farm_roles` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `farm_role_scaling_metrics`
--
ALTER TABLE `farm_role_scaling_metrics`
  ADD CONSTRAINT `farm_role_scaling_metrics_ibfk_1` FOREIGN KEY (`farm_roleid`) REFERENCES `farm_roles` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `farm_role_scripting_params`
--
ALTER TABLE `farm_role_scripting_params`
  ADD CONSTRAINT `farm_role_scripting_params_ibfk_3` FOREIGN KEY (`farm_role_id`) REFERENCES `farm_roles` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `farm_role_scripting_targets`
--
ALTER TABLE `farm_role_scripting_targets`
  ADD CONSTRAINT `farm_role_scripting_targets_ibfk_3` FOREIGN KEY (`farm_role_script_id`) REFERENCES `farm_role_scripts` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `farm_role_storage_devices`
--
ALTER TABLE `farm_role_storage_devices`
  ADD CONSTRAINT `farm_role_storage_devices_ibfk_1` FOREIGN KEY (`storage_config_id`) REFERENCES `farm_role_storage_config` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `farm_role_storage_settings`
--
ALTER TABLE `farm_role_storage_settings`
  ADD CONSTRAINT `farm_role_storage_settings_ibfk_1` FOREIGN KEY (`storage_config_id`) REFERENCES `farm_role_storage_config` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `global_variables`
--
ALTER TABLE `global_variables`
  ADD CONSTRAINT `global_variables_ibfk_1` FOREIGN KEY (`env_id`) REFERENCES `client_environments` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `role_behaviors`
--
ALTER TABLE `role_behaviors`
  ADD CONSTRAINT `role_behaviors_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `role_images`
--
ALTER TABLE `role_images`
  ADD CONSTRAINT `role_images_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `role_parameters`
--
ALTER TABLE `role_parameters`
  ADD CONSTRAINT `role_parameters_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `role_properties`
--
ALTER TABLE `role_properties`
  ADD CONSTRAINT `role_properties_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `role_scripts`
--
ALTER TABLE `role_scripts`
  ADD CONSTRAINT `role_scripts_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `role_scripts_ibfk_2` FOREIGN KEY (`script_id`) REFERENCES `scripts` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `role_security_rules`
--
ALTER TABLE `role_security_rules`
  ADD CONSTRAINT `role_security_rules_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `role_software`
--
ALTER TABLE `role_software`
  ADD CONSTRAINT `role_software_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `role_tags`
--
ALTER TABLE `role_tags`
  ADD CONSTRAINT `role_tags_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `server_alerts`
--
ALTER TABLE `server_alerts`
  ADD CONSTRAINT `server_alerts_ibfk_1` FOREIGN KEY (`farm_id`) REFERENCES `farms` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `server_alerts_ibfk_2` FOREIGN KEY (`env_id`) REFERENCES `client_environments` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `server_operations`
--
ALTER TABLE `server_operations`
  ADD CONSTRAINT `server_operations_ibfk_1` FOREIGN KEY (`server_id`) REFERENCES `servers` (`server_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `server_operation_progress`
--
ALTER TABLE `server_operation_progress`
  ADD CONSTRAINT `server_operation_progress_ibfk_1` FOREIGN KEY (`operation_id`) REFERENCES `server_operations` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `server_properties`
--
ALTER TABLE `server_properties`
  ADD CONSTRAINT `server_properties_ibfk_1` FOREIGN KEY (`server_id`) REFERENCES `servers` (`server_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `services_db_backups`
--
ALTER TABLE `services_db_backups`
  ADD CONSTRAINT `services_db_backups_ibfk_1` FOREIGN KEY (`env_id`) REFERENCES `client_environments` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `services_db_backups_history`
--
ALTER TABLE `services_db_backups_history`
  ADD CONSTRAINT `services_db_backups_history_ibfk_1` FOREIGN KEY (`farm_role_id`) REFERENCES `farm_roles` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `services_db_backup_parts`
--
ALTER TABLE `services_db_backup_parts`
  ADD CONSTRAINT `services_db_backup_parts_ibfk_1` FOREIGN KEY (`backup_id`) REFERENCES `services_db_backups` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `services_mongodb_config_servers`
--
ALTER TABLE `services_mongodb_config_servers`
  ADD CONSTRAINT `services_mongodb_config_servers_ibfk_1` FOREIGN KEY (`farm_role_id`) REFERENCES `farm_roles` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

