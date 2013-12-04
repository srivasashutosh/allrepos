<?
    class ScalrEnvironment20081125 extends ScalrEnvironment
    {
        protected function ListScripts()
        {
            $ResponseDOMDocument = $this->CreateResponse();

            $ScriptsDOMNode = $ResponseDOMDocument->createElement("scripts");

            if (!$this->DBServer->IsSupported(0.5)) {
                throw new Exception("ami-scripts roles cannot execute scripts anymore. Please upgrade your roles to scalarizr: http://scalr.net/blog/announcements/ami-scripts/");
            } elseif (!$this->DBServer->IsSupported(0.9) && $this->DBServer->IsSupported(0.8)) {
                throw new Exception("Windows scalarizr doesn't support script executions");
            } else {
                if (SCALR_ID == 'ab6d8171') {
                    $this->DB->Execute("INSERT INTO debug_scripting SET
                        server_id = ?,
                        request = ?,
                        params = ?
                    ", array(
                        $this->DBServer->serverId,
                        json_encode($_REQUEST),
                        json_encode(array(
                            $this->DBServer->GetProperty(SERVER_PROPERTIES::SZR_VESION),
                            $_SERVER['REQUEST_URI']
                        ))
                    ));
                }
            }

            $ResponseDOMDocument->documentElement->appendChild($ScriptsDOMNode);

            return $ResponseDOMDocument;
        }

        protected function ListVirtualhosts()
        {
            $ResponseDOMDocument = $this->CreateResponse();

            $type = $this->GetArg("type");
            $name = $this->GetArg("name");
            $https = $this->GetArg("https");

            $virtual_hosts = $this->DB->GetAll("SELECT * FROM apache_vhosts WHERE farm_roleid=?",
                array($this->DBServer->farmRoleId)
            );

            $VhostsDOMNode = $ResponseDOMDocument->createElement("vhosts");

            $DBFarmRole = $this->DBServer->GetFarmRoleObject();

            if ($DBFarmRole->GetRoleObject()->hasBehavior(ROLE_BEHAVIORS::NGINX))
            {
                $vhost_info = $this->DB->GetRow("SELECT * FROM apache_vhosts WHERE farm_id=? AND is_ssl_enabled='1'",
                    array($this->DBServer->farmId)
                );

                if ($vhost_info)
                {
                    $template = $this->DB->GetOne("SELECT value FROM farm_role_options WHERE hash IN ('nginx_https_vhost_template') AND farm_roleid=?",
                        array($DBFarmRole->ID)
                    );
                    if (!$template) {
                        $template = $this->DB->GetOne("SELECT defval FROM role_parameters WHERE role_id=? AND hash IN ('nginx_https_vhost_template')",
                            array($DBFarmRole->RoleID)
                        );

                        if (!$template)
                            $template = file_get_contents(APPPATH."/templates/services/nginx/ssl.vhost.tpl");
                    }

                    if ($template) {
                        $vars = unserialize($vhost_info['httpd_conf_vars']);
                        $vars['host'] = $vhost_info['name'];
                        $vKeys = array_keys($vars);

                        $f = create_function('$item', 'return "{\$".$item."}";');
                        $keys = array_map($f, $vKeys);
                        $vValues = array_values($vars);

                        $contents = str_replace($keys, $vValues, $template);
                        $contents = str_replace(array("{literal}", "{/literal}"), array("", ""), $contents);

                        $VhostDOMNode =  $ResponseDOMDocument->createElement("vhost");
                        $VhostDOMNode->setAttribute("hostname", $vhost_info['name']);
                        $VhostDOMNode->setAttribute("https", "1");
                        $VhostDOMNode->setAttribute("type", "nginx");

                        $RawDOMNode = $ResponseDOMDocument->createElement("raw");
                        $RawDOMNode->appendChild($ResponseDOMDocument->createCDATASection($contents));

                        $VhostDOMNode->appendChild($RawDOMNode);
                        $VhostsDOMNode->appendChild($VhostDOMNode);
                    }
                    else
                        throw new Exception("Virtualhost template not found in database. (farm roleid: {$DBFarmRole->ID})");
                }
            }
            elseif ($DBFarmRole->GetRoleObject()->hasBehavior(ROLE_BEHAVIORS::APACHE))
            {
                while (count($virtual_hosts) > 0)
                {
                    $virtualhost = array_shift($virtual_hosts);

                    if ($virtualhost['is_ssl_enabled'])
                    {
                        $nonssl_vhost = $virtualhost;
                        $nonssl_vhost['is_ssl_enabled'] = 0;
                        array_push($virtual_hosts, $nonssl_vhost);
                    }

                    //Filter by name
                    if ($this->GetArg("name") !== null && $this->GetArg("name") != $virtualhost['name'])
                        continue;

                    // Filter by https
                    if ($this->GetArg("https") !== null && $virtualhost['is_ssl_enabled'] != $this->GetArg("https"))
                        continue;

                    $VhostDOMNode =  $ResponseDOMDocument->createElement("vhost");
                    $VhostDOMNode->setAttribute("hostname", $virtualhost['name']);
                    $VhostDOMNode->setAttribute("https", $virtualhost['is_ssl_enabled']);
                    $VhostDOMNode->setAttribute("type", "apache");

                    $vars = unserialize($virtualhost['httpd_conf_vars']);
                    $vars['host'] = $virtualhost['name'];
                    $vKeys = array_keys($vars);

                    $f = create_function('$item', 'return "{\$".$item."}";');
                    $keys = array_map($f, $vKeys);
                    $vValues = array_values($vars);

                    if (!$virtualhost['is_ssl_enabled'])
                        $template = $virtualhost['httpd_conf'];
                    else
                        $template = $virtualhost['httpd_conf_ssl'];

                    $contents = str_replace($keys, $vValues, $template);
                    $contents = str_replace(array("{literal}", "{/literal}"), array("", ""), $contents);

                    $RawDOMNode = $ResponseDOMDocument->createElement("raw");
                    $RawDOMNode->appendChild($ResponseDOMDocument->createCDATASection($contents));

                    $VhostDOMNode->appendChild($RawDOMNode);
                    $VhostsDOMNode->appendChild($VhostDOMNode);
                }
            }

            $ResponseDOMDocument->documentElement->appendChild($VhostsDOMNode);

            return $ResponseDOMDocument;
        }

        protected function ListRoleParams()
        {
            $ResponseDOMDocument = $this->CreateResponse();
            $ParamsDOMNode = $ResponseDOMDocument->createElement("params");


            $DBFarmRole = $this->DBServer->GetFarmRoleObject();
            $dbRole = $DBFarmRole->GetRoleObject();
            $params = $dbRole->getParameters();
            foreach ($params as $param) {
                if ($this->GetArg("name") && $this->GetArg("name") != $param['hash'])
                    continue;

                $farmRoleOption = $this->DB->GetRow("SELECT id, value FROM farm_role_options WHERE farm_roleid=? AND `hash`=?", array($DBFarmRole->ID, $param['hash']));
                if ($farmRoleOption['id'])
                    $value = $farmRoleOption['value'];
                else
                    $value = $param['defval'];

                $ParamDOMNode = $ResponseDOMDocument->createElement("param");
                $ParamDOMNode->setAttribute("name", $param['hash']);

                $ValueDomNode = $ResponseDOMDocument->createElement("value");
                $ValueDomNode->appendChild($ResponseDOMDocument->createCDATASection($value));

                $ParamDOMNode->appendChild($ValueDomNode);
                $ParamsDOMNode->appendChild($ParamDOMNode);
            }

            $ResponseDOMDocument->documentElement->appendChild($ParamsDOMNode);

            return $ResponseDOMDocument;
        }

        /**
         * Return HTTPS certificate and private key
         * @return DOMDocument
         */
        protected function GetHttpsCertificate()
        {
            $ResponseDOMDocument = $this->CreateResponse();

            if ($this->DBServer->status == SERVER_STATUS::PENDING_TERMINATE || $this->DBServer->status == SERVER_STATUS::TERMINATED  || $this->DBServer->status == SERVER_STATUS::TROUBLESHOOTING)
                return $ResponseDOMDocument;

            $hostName = $this->GetArg("hostname") ? " AND name=".$this->qstr($this->GetArg("hostname")) : "";

            if ($this->GetArg("id")) {
                $sslInfo = $this->DB->GetRow("SELECT * FROM services_ssl_certs WHERE id = ? AND env_id = ?", array(
                    $this->GetArg("id"),
                    $this->DBServer->envId
                ));
            } else {
                if ($this->DBServer->GetFarmRoleObject()->GetRoleObject()->hasBehavior(ROLE_BEHAVIORS::NGINX)) {
                    $vhost_info = $this->DB->GetRow("SELECT * FROM apache_vhosts WHERE farm_id=? AND is_ssl_enabled='1' {$hostName}",
                        array($this->DBServer->farmId)
                    );
                }
                else {
                    $vhost_info = $this->DB->GetRow("SELECT * FROM apache_vhosts WHERE farm_roleid=? AND is_ssl_enabled='1' {$hostName}",
                        array($this->DBServer->farmRoleId)
                    );
                }

                if ($vhost_info)
                    $sslInfo = $this->DB->GetRow("SELECT * FROM services_ssl_certs WHERE id = ? AND env_id = ?", array(
                        $vhost_info['ssl_cert_id'],
                        $this->DBServer->envId
                    ));
            }

            if ($sslInfo) {

                $vhost = $ResponseDOMDocument->createElement("virtualhost");
                $vhost->setAttribute("name", $sslInfo['name']);

                $vhost->appendChild(
                    $ResponseDOMDocument->createElement("cert", $sslInfo['ssl_cert'])
                );
                $vhost->appendChild(
                    $ResponseDOMDocument->createElement("pkey", $sslInfo['ssl_pkey'])
                );
                $vhost->appendChild(
                    $ResponseDOMDocument->createElement("ca_cert", $sslInfo['ssl_cabundle'])
                );

                $ResponseDOMDocument->documentElement->appendChild(
                    $vhost
                );
            }

            return $ResponseDOMDocument;
        }

        /**
         * List farm roles and hosts list for each role
         * Allowed args: role=(String Role Name) | behaviour=(app|www|mysql|base|memcached)
         * @return DOMDocument
         */
        protected function ListRoles()
        {
            $ResponseDOMDocument = $this->CreateResponse();

            $RolesDOMNode = $ResponseDOMDocument->createElement('roles');
            $ResponseDOMDocument->documentElement->appendChild($RolesDOMNode);

            $sql_query = "SELECT id FROM farm_roles WHERE farmid=?";
            $sql_query_args = array($this->DBServer->farmId);

            // Filter by behaviour
            if ($this->GetArg("behaviour"))
            {
                $sql_query .= " AND role_id IN (SELECT role_id FROM role_behaviors WHERE behavior=?)";
                array_push($sql_query_args, $this->GetArg("behaviour"));
            }

            // Filter by role
            if ($this->GetArg("role"))
            {
                $sql_query .= " AND role_id IN (SELECT id FROM roles WHERE name=?)";
                array_push($sql_query_args, $this->GetArg("role"));
            }

            if ($this->GetArg("role-id"))
            {
                $sql_query .= " AND role_id = ?";
                array_push($sql_query_args, $this->GetArg("role-id"));
            }

            if ($this->GetArg("farm-role-id"))
            {
                $sql_query .= " AND id = ?";
                array_push($sql_query_args, $this->GetArg("farm-role-id"));
            }

            $farm_roles = $this->DB->GetAll($sql_query, $sql_query_args);
            foreach ($farm_roles as $farm_role)
            {
                $DBFarmRole = DBFarmRole::LoadByID($farm_role['id']);

                $roleId = $DBFarmRole->NewRoleID ? $DBFarmRole->NewRoleID : $DBFarmRole->RoleID;

                // Create role node
                $RoleDOMNode = $ResponseDOMDocument->createElement('role');
                $RoleDOMNode->setAttribute('behaviour', implode(",", $DBFarmRole->GetRoleObject()->getBehaviors()));
                $RoleDOMNode->setAttribute('name', DBRole::loadById($roleId)->name);
                $RoleDOMNode->setAttribute('id', $DBFarmRole->ID);
                $RoleDOMNode->setAttribute('role-id', $roleId);

                $HostsDomNode = $ResponseDOMDocument->createElement('hosts');
                $RoleDOMNode->appendChild($HostsDomNode);

                // List instances (hosts)
                $serversSql = "SELECT server_id FROM servers WHERE farm_roleid=?";
                $serversArgs = array($farm_role['id'], SERVER_STATUS::RUNNING);

                if ($this->GetArg("showInitServers")) {
                    $serversSql .= " AND status IN (?,?)";
                    $serversArgs[] = SERVER_STATUS::INIT;
                } else {
                    $serversSql .= " AND status=?";
                }

                $servers = $this->DB->GetAll($serversSql, $serversArgs);

                // Add hosts to response
                if (count($servers) > 0)
                {
                    foreach ($servers as $server)
                    {
                        $DBServer = DBServer::LoadByID($server['server_id']);

                        $HostDOMNode = $ResponseDOMDocument->createElement("host");
                        $HostDOMNode->setAttribute('internal-ip', $DBServer->localIp);
                        $HostDOMNode->setAttribute('external-ip', $DBServer->remoteIp);
                        $HostDOMNode->setAttribute('index', $DBServer->index);
                        $HostDOMNode->setAttribute('status', $DBServer->status);
                        $HostDOMNode->setAttribute('cloud-location', $DBServer->GetCloudLocation());

                        if ($DBFarmRole->GetRoleObject()->hasBehavior(ROLE_BEHAVIORS::MONGODB))
                        {
                            $HostDOMNode->setAttribute('replica-set-index', (int)$DBServer->GetProperty(Scalr_Role_Behavior_MongoDB::SERVER_REPLICA_SET_INDEX));
                            $HostDOMNode->setAttribute('shard-index', (int)$DBServer->GetProperty(Scalr_Role_Behavior_MongoDB::SERVER_SHARD_INDEX));
                        }

                        if ($DBFarmRole->GetRoleObject()->hasBehavior(ROLE_BEHAVIORS::MYSQL))
                            $HostDOMNode->setAttribute('replication-master', (int)$DBServer->GetProperty(SERVER_PROPERTIES::DB_MYSQL_MASTER));

                        if ($DBFarmRole->GetRoleObject()->getDbMsrBehavior())
                            $HostDOMNode->setAttribute('replication-master', (int)$DBServer->GetProperty(Scalr_Db_Msr::REPLICATION_MASTER));

                        $HostsDomNode->appendChild($HostDOMNode);
                    }
                }

                // Add role node to roles node
                $RolesDOMNode->appendChild($RoleDOMNode);
            }

            return $ResponseDOMDocument;
        }
    }

?>