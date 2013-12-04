<?php

    class ServerCreateInfo
    {
        public $platform;

        /**
         *
         * @var DBFarmRole
         */
        public $dbFarmRole;
        public $index;
        public $remoteIp;
        public $localIp;
        public $clientId;
        public $envId;
        public $roleId;
        public $farmId;

        private $platformProps = array();

        private $properties;

        /**
         *
         * @param string $platform (From SERVER_PLATFORMS class)
         * @param integer $farmid
         * @param integer $farm_roleid
         * @param integer $index
         * @return void
         */
        public function __construct($platform, DBFarmRole $DBFarmRole = null, $index = null, $role_id = null)
        {
            $this->platform = $platform;
            $this->dbFarmRole = $DBFarmRole;
            $this->index = $index;
            $this->roleId = $role_id === null ? $this->dbFarmRole->RoleID : $role_id;

            if ($DBFarmRole)
                $this->envId = $DBFarmRole->GetFarmObject()->EnvID;


            //Refletcion
            $Reflect = new ReflectionClass(DBServer::$platformPropsClasses[$this->platform]);
            foreach ($Reflect->getConstants() as $k=>$v)
                $this->platformProps[] = $v;

            if ($DBFarmRole)
            {
                switch($this->platform)
                {
                    case SERVER_PLATFORMS::NIMBULA:

                        break;

                    case SERVER_PLATFORMS::EUCALYPTUS:
                        $this->SetProperties(array(
                            EUCA_SERVER_PROPERTIES::REGION => $DBFarmRole->CloudLocation
                        ));
                        break;

                    case SERVER_PLATFORMS::OPENSTACK:
                        $this->SetProperties(array(
                            OPENSTACK_SERVER_PROPERTIES::CLOUD_LOCATION => $DBFarmRole->CloudLocation
                        ));
                        break;

                    case SERVER_PLATFORMS::RACKSPACE:
                        $this->SetProperties(array(
                            RACKSPACE_SERVER_PROPERTIES::DATACENTER => $DBFarmRole->CloudLocation
                        ));
                        break;

                    case SERVER_PLATFORMS::GCE:
                        $this->SetProperties(array(
                            GCE_SERVER_PROPERTIES::CLOUD_LOCATION => $DBFarmRole->CloudLocation
                        ));
                        break;

                    case SERVER_PLATFORMS::UCLOUD:
                    case SERVER_PLATFORMS::IDCF:
                    case SERVER_PLATFORMS::CLOUDSTACK:
                        $this->SetProperties(array(
                            CLOUDSTACK_SERVER_PROPERTIES::CLOUD_LOCATION => $DBFarmRole->CloudLocation
                        ));
                        break;

                    case SERVER_PLATFORMS::EC2:
                        $this->SetProperties(array(
                            //EC2_SERVER_PROPERTIES::AVAIL_ZONE => $DBFarmRole->GetSetting(DBFarmRole::SETTING_AWS_AVAIL_ZONE),
                            EC2_SERVER_PROPERTIES::REGION => $DBFarmRole->CloudLocation
                        ));
                    break;
                }

                //TODO:
                $this->SetProperties(array(SERVER_PROPERTIES::SZR_VESION => '0.13.0'));
            }
            else
                $this->SetProperties(array(SERVER_PROPERTIES::SZR_VESION => '0.13.0'));
        }

        public function SetProperties(array $props)
        {
            foreach($props as $k=>$v)
            {
                if (in_array($k, $this->platformProps))
                    $this->properties[$k] = $v;
                else
                    throw new Exception(sprintf("Unknown property '%s' for server on '%s'", $k, $this->platform));
            }
        }

        public function GetProperty($propName)
        {
            if (in_array($propName, $this->platformProps))
                return $this->properties[$propName];
            else
                throw new Exception(sprintf("Unknown property '%s' for server on '%s'", $propName, $this->platform));
        }

        public function GetProperties()
        {
            return $this->properties;
        }
    }

?>