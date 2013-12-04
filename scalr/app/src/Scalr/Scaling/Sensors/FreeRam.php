<?php
    class Scalr_Scaling_Sensors_FreeRam extends Scalr_Scaling_Sensor
    {
        private $snmpOids = array(
            'memswap' => ".1.3.6.1.4.1.2021.4.11.0",
            'cachedram' => ".1.3.6.1.4.1.2021.4.15.0",
            'mem'	  => ".1.3.6.1.4.1.2021.4.6.0"
        );

        const SETTING_USE_CACHED = 'use_cached';

        public $isInvert = true;

        public function __construct()
        {
            $this->snmpClient = new Scalr_Net_Snmp_Client();
        }

        public function getValue(DBFarmRole $dbFarmRole, Scalr_Scaling_FarmRoleMetric $farmRoleMetric)
        {
            $servers = $dbFarmRole->GetServersByFilter(array('status' => SERVER_STATUS::RUNNING));
            $dbFarm = $dbFarmRole->GetFarmObject();

            if (count($servers) == 0)
                return false;

            $retval = array();

            foreach ($servers as $DBServer)
            {
                if ($dbFarmRole->GetSetting(DBFarmRole::SETTING_SCALING_EXCLUDE_DBMSR_MASTER) == 1) {
                    $isMaster = ($DBServer->GetProperty(SERVER_PROPERTIES::DB_MYSQL_MASTER) == 1 || $DBServer->GetProperty(Scalr_Db_Msr::REPLICATION_MASTER) == 1);
                    if ($isMaster)
                        continue;
                }

                if ($DBServer->IsSupported('0.13.0')) {
                    $szrClient = Scalr_Net_Scalarizr_Client::getClient(
                        $DBServer,
                        Scalr_Net_Scalarizr_Client::NAMESPACE_SYSTEM,
                        $DBServer->getPort(DBServer::PORT_API)
                    );

                    $ramUsage = $szrClient->memInfo();
                    $ram = (float)$ramUsage->total_free;

                    if ($farmRoleMetric->getSetting(self::SETTING_USE_CACHED))
                        $ram = $ram+(float)$ramUsage->cached;

                } else {
                    $port = $DBServer->GetProperty(SERVER_PROPERTIES::SZR_SNMP_PORT);

                    $this->snmpClient->connect($DBServer->remoteIp, $port ? $port : 161, $dbFarm->Hash, null, null, false);
                    $res = $this->snmpClient->get(
                        $this->snmpOids['memswap']
                    );

                    preg_match_all("/[0-9]+/si", $res, $matches);
                    $ram = (float)$matches[0][0];

                    if ($farmRoleMetric->getSetting(self::SETTING_USE_CACHED)) {
                        $res = $this->snmpClient->get(
                            $this->snmpOids['cachedram']
                        );

                        preg_match_all("/[0-9]+/si", $res, $matches);
                        $cram = (float)$matches[0][0];

                        $ram = $ram+$cram;
                    }
                }

                $retval[] = round($ram/1024, 2);
            }

            return $retval;
        }
    }