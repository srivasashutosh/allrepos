<?php

use Scalr\Farm\Role\FarmRoleStorage;

    class ScalrEnvironment20120701 extends ScalrEnvironment20120417
    {
        public function GetGlobalConfig()
        {
            $ResponseDOMDocument = $this->CreateResponse();
            $configNode = $ResponseDOMDocument->createElement("settings");

            $config = array(
                'dns.static.endpoint' => \Scalr::config('scalr.dns.static.domain_name'),
                'scalr.version'       => SCALR_VERSION,
                'scalr.id'            => SCALR_ID
            );

            foreach ($config as $key => $value) {
                $settingNode = $ResponseDOMDocument->createElement("setting", $value);
                $settingNode->setAttribute("key", $key);
                $configNode->appendChild($settingNode);
            }


            $ResponseDOMDocument->documentElement->appendChild($configNode);
            return $ResponseDOMDocument;
        }

        public function SetGlobalVariable()
        {
            $scope = $this->GetArg("scope");
            $paramName = $this->GetArg("param-name");
            $paramValue = $this->GetArg("param-value");
            $final = (int)$this->GetArg("flag-final");

            $globalVariables = new Scalr_Scripting_GlobalVariables($this->DBServer->envId, $scope);
            $globalVariables->setValues(
                array(array(
                    'name' 	=> $paramName,
                    'value'	=> $paramValue,
                    'flagFinal' => $final,
                    'flagRequired' => 0,
                    'scope' => $scope
                )),
                $this->DBServer->roleId,
                $this->DBServer->farmId,
                $this->DBServer->farmRoleId
            );

            $ResponseDOMDocument = $this->CreateResponse();
            $configNode = $ResponseDOMDocument->createElement("variables");

            $settingNode = $ResponseDOMDocument->createElement("variable", $paramValue);
            $settingNode->setAttribute("name", $paramName);
            $configNode->appendChild($settingNode);

            $ResponseDOMDocument->documentElement->appendChild($configNode);
            return $ResponseDOMDocument;
        }

        public function ListGlobalVariables()
        {
            $ResponseDOMDocument = $this->CreateResponse();
            $configNode = $ResponseDOMDocument->createElement("variables");

            $globalVariables = new Scalr_Scripting_GlobalVariables($this->DBServer->envId, Scalr_Scripting_GlobalVariables::SCOPE_FARMROLE);
            $vars = $globalVariables->listVariables($this->DBServer->roleId, $this->DBServer->farmId, $this->DBServer->farmRoleId);
            foreach ($vars as $key => $value) {
                $settingNode = $ResponseDOMDocument->createElement("variable", $value);
                $settingNode->setAttribute("name", $key);
                $configNode->appendChild($settingNode);
            }

            foreach ($this->DBServer->GetScriptingVars() as $name => $value) {
                $settingNode = $ResponseDOMDocument->createElement("variable", $value);
                $settingNode->setAttribute("name", "SCALR_".strtoupper($name));
                $configNode->appendChild($settingNode);
            }

            $ResponseDOMDocument->documentElement->appendChild($configNode);
            return $ResponseDOMDocument;
        }

        public function ListFarmRoleParams()
        {
            $farmRoleId = $this->GetArg("farm-role-id");
            if (!$farmRoleId)
                throw new Exception("'farm-role-id' required");

            $dbFarmRole = DBFarmRole::LoadByID($farmRoleId);
            if ($dbFarmRole->FarmID != $this->DBServer->farmId)
                throw new Exception("You can request this information ONLY for roles within server farm");

            $ResponseDOMDocument = $this->CreateResponse();

            // Add volumes information
            try {
                if ($this->DBServer->farmRoleId == $farmRoleId) {
                    $storage = new FarmRoleStorage($dbFarmRole);
                    $vols = $storage->getVolumesConfigs($this->DBServer->index);
                    $volumes = array();
                    foreach ($vols as $i => $volume) {
                        if ($volume->id)
                            $volumes[] = $volume;
                    }

                    $bodyEl = $this->serialize($volumes, 'volumes', $ResponseDOMDocument);
                    $ResponseDOMDocument->documentElement->appendChild($bodyEl);
                }
            } catch (Exception $e) {
                $this->Logger->fatal("ListFarmRoleParams::Storage: {$e->getMessage()}");
            }


            $role = $dbFarmRole->GetRoleObject();
            $behaviors = $role->getBehaviors();
            foreach ($behaviors as $behavior) {
                $data = null;

                if ($behavior == ROLE_BEHAVIORS::MONGODB)
                    $data = Scalr_Role_Behavior::loadByName($behavior)->getConfiguration($this->DBServer);


                if ($behavior == ROLE_BEHAVIORS::CHEF)
                    $data = Scalr_Role_Behavior::loadByName($behavior)->getConfiguration($this->DBServer);


                if ($behavior == ROLE_BEHAVIORS::CF_CLOUD_CONTROLLER) {
                    $data = new stdClass();
                    $data->version = $dbFarmRole->GetSetting(Scalr_Role_Behavior_CfCloudController::ROLE_VERSION);
                }
                else if ($behavior == ROLE_BEHAVIORS::MYSQL) {
                    $data = new stdClass();
                    $data->logFile = $dbFarmRole->GetSetting(DBFarmRole::SETTING_MYSQL_LOG_FILE);
                    $data->logPos = $dbFarmRole->GetSetting(DBFarmRole::SETTING_MYSQL_LOG_POS);
                    $data->rootPassword = $dbFarmRole->GetSetting(DBFarmRole::SETTING_MYSQL_ROOT_PASSWORD);
                    $data->replPassword = $dbFarmRole->GetSetting(DBFarmRole::SETTING_MYSQL_REPL_PASSWORD);
                    $data->statPassword = $dbFarmRole->GetSetting(DBFarmRole::SETTING_MYSQL_STAT_PASSWORD);
                    $data->replicationMaster = (int)$this->DBServer->GetProperty(SERVER_PROPERTIES::DB_MYSQL_MASTER);
                    //TODO: Storage

                } else {

                    try {
                        $dbMsrInfo = Scalr_Db_Msr_Info::init($dbFarmRole, $this->DBServer, $behavior);
                        $data = $dbMsrInfo->getMessageProperties();
                    } catch (Exception $e) {
                        //$this->Logger->fatal("ListFarmRoleParams::{$behavior}: {$e->getMessage()}");
                    }
                }

                if ($data) {
                    $bodyEl = $this->serialize($data, $behavior, $ResponseDOMDocument);
                    $ResponseDOMDocument->documentElement->appendChild($bodyEl);
                }
            }

            return $ResponseDOMDocument;
        }

        private function serialize ($object, $behavior, $doc) {

            $bodyEl = $doc->createElement($behavior);
            $body = array();
            if (is_object($object)) {
                foreach (get_object_vars($object) as $k => $v) {
                    $body[$k] = $v;
                }
            } else {
                $body = $object;
            }

            $this->walkSerialize($body, $bodyEl, $doc);

            return $bodyEl;
        }

        private function walkSerialize ($value, $el, $doc) {
            if (is_array($value) || is_object($value)) {
                if (is_array($value) && array_keys($value) === range(0, count($value)-1)) {
                    // Numeric indexes array
                    foreach ($value as $v) {
                        $itemEl = $doc->createElement("item");
                        $el->appendChild($itemEl);
                        $this->walkSerialize($v, $itemEl, $doc);
                    }
                } else {
                    // Assoc arrays and objects
                    foreach ($value as $k => $v) {
                        $itemEl = $doc->createElement($this->under_scope($k));
                        $el->appendChild($itemEl);
                        $this->walkSerialize($v, $itemEl, $doc);
                    }
                }
            } else {
                if (preg_match("/[\<\>\&]+/", $value)) {
                    $valueEl = $doc->createCDATASection($value);
                } else {
                    $valueEl = $doc->createTextNode($value);
                }
                $el->appendChild($valueEl);
            }
        }

        private function under_scope ($name) {
            $parts = preg_split("/[A-Z]/", $name, -1, PREG_SPLIT_OFFSET_CAPTURE | PREG_SPLIT_NO_EMPTY);
            $ret = "";
            foreach ($parts as $part) {
                if ($part[1]) {
                    $ret .= "_" . strtolower($name{$part[1]-1});
                }
                $ret .= $part[0];
            }
            return $ret;
        }
    }
