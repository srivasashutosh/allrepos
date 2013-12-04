<?php
use Scalr\Service\Aws\Elb\DataType\ListenerList;
use Scalr\Service\Aws\Elb\DataType\ListenerData;
use Scalr\Service\Aws\Elb\DataType\LoadBalancerDescriptionData;
use Scalr\Service\Aws\Elb\DataType\HealthCheckData;
use Scalr\Service\Aws\Elb\DataType\InstanceStateData;
use Scalr\Farm\Role\FarmRoleService;

class Modules_Platforms_Ec2_Helpers_Elb
{
    public static function farmValidateRoleSettings($settings, $rolename)
    {
    }

    public static function farmUpdateRoleSettings(DBFarmRole $DBFarmRole, $oldSettings, $newSettings)
    {
        //Conver OLD ELB settings into NEW ELB SETTINGS
        if ($newSettings[DBFarmRole::SETTING_BALANCING_USE_ELB] == 1 && !$newSettings[DBFarmRole::SETTING_AWS_ELB_ENABLED]) {
            $newSettings[DBFarmRole::SETTING_AWS_ELB_ENABLED] = 1;
            $newSettings[DBFarmRole::SETTING_AWS_ELB_ID] = $newSettings[DBFarmRole::SETTING_BALANCING_NAME];
            $DBFarmRole->SetSetting(DBFarmRole::SETTING_AWS_ELB_ENABLED, 1);
            $DBFarmRole->SetSetting(DBFarmRole::SETTING_AWS_ELB_ID, $newSettings[DBFarmRole::SETTING_BALANCING_NAME]);
        }

        //NEW ELB:
        try {
            $DBFarm = $DBFarmRole->GetFarmObject();
            $elb = $DBFarm->GetEnvironmentObject()->aws($DBFarmRole)->elb;

            /*
             * aws.elb.enabled
             * aws.elb.id":"scalr-97f8a108ce4100-775",
             * aws.elb.remove
             */
            if ($newSettings[DBFarmRole::SETTING_AWS_ELB_ENABLED]) {
                if ($oldSettings[DBFarmRole::SETTING_AWS_ELB_ID] == $newSettings[DBFarmRole::SETTING_AWS_ELB_ID])
                    return true;

                // Setup new service
                // ADD ELB to role_cloud_services
                $service = new FarmRoleService($DBFarmRole, $newSettings[DBFarmRole::SETTING_AWS_ELB_ID]);
                $service->setType(FarmRoleService::SERVICE_AWS_ELB);
                $service->save();

                // Add running instances to ELB
                $servers = $DBFarmRole->GetServersByFilter(array('status' => SERVER_STATUS::RUNNING));
                $newInstances = array();
                foreach ($servers as $DBServer) {
                    $newInstances[] = $DBServer->GetProperty(EC2_SERVER_PROPERTIES::INSTANCE_ID);
                }

                try {
                    if (count($newInstances) > 0)
                        $elb->loadBalancer->registerInstances($newSettings[DBFarmRole::SETTING_AWS_ELB_ID], $newInstances);
                } catch (Exception $e) {}

                try {
                    //Check and deregister old instances instances
                    $list = $elb->loadBalancer->describeInstanceHealth($newSettings[DBFarmRole::SETTING_AWS_ELB_ID], array());
                    /* @var $instance \Scalr\Service\Aws\Elb\DataType\InstanceStateData */
                    $instances = array();
                    foreach ($list as $instance) {
                        if (!in_array($instance->instanceId, $newInstances))
                            array_push($instances, $instance->instanceId);
                    }

                    if (count($instances) > 0)
                        $elb->loadBalancer->deregisterInstances($newSettings[DBFarmRole::SETTING_AWS_ELB_ID], $instances);
                } catch (Exception $e) {}

            } else {
                $clearSettings = true;
            }

            if ($newSettings['aws.elb.remove']) {
                if ($newSettings[DBFarmRole::SETTING_AWS_ELB_ID])
                    $elb->loadBalancer->delete($newSettings[DBFarmRole::SETTING_AWS_ELB_ID]);

                $clearSettings = true;
            }

            if ($clearSettings) {
                $DBFarmRole->ClearSettings("aws.elb.");
                $service = new FarmRoleService($DBFarmRole, $newSettings[DBFarmRole::SETTING_AWS_ELB_ID]);
                $service->remove();
            }

            // Check and remove OLD ELB settings
            if ($newSettings['aws.elb.enabled'] && $DBFarmRole->GetSetting(DBFarmRole::SETTING_BALANCING_HOSTNAME)) {
                $DBFarmRole->SetSetting(DBFarmRole::SETTING_BALANCING_NAME, null);
                $DBFarmRole->SetSetting(DBFarmRole::SETTING_BALANCING_HOSTNAME, null);
                $DBFarmRole->SetSetting(DBFarmRole::SETTING_BALANCING_USE_ELB, null);
                $DBFarmRole->SetSetting(DBFarmRole::SETTING_BALANCING_HC_HASH, null);
                $DBFarmRole->ClearSettings("lb.avail_zone");
                $DBFarmRole->ClearSettings("lb.healthcheck");
                $DBFarmRole->ClearSettings("lb.role.listener");
            }
        } catch (Exception $e) {
            throw new Exception("Error with ELB on Role '{$DBFarmRole->GetRoleObject()->name}': {$e->getMessage()}");
        }

        //OLD ELB
        /*
        try {
            // Load balancer settings
            if ($newSettings[DBFarmRole::SETTING_BALANCING_USE_ELB] == 1) {
                // Listeners
                $DBFarmRole->ClearSettings("lb.role.listener");
                $listenersList = new ListenerList();
                $li = 0;
                foreach ($newSettings as $sk => $sv) {
                    if (stristr($sk, "lb.role.listener")) {
                        $li++;
                        $listener_chunks = explode("#", $sv);
                        $listenersList->append(new ListenerData(
                            trim($listener_chunks[1]), trim($listener_chunks[2]),
                            trim($listener_chunks[0]), null,
                            trim($listener_chunks[3])
                        ));
                        $DBFarmRole->SetSetting("lb.role.listener.{$li}", str_replace(" ", "", $sv));
                    }
                }
                $avail_zones = array();
                $avail_zones_setting_hash = "";
                foreach ($newSettings as $skey => $sval) {
                    if (preg_match("/^lb.avail_zone.(.*)?$/", $skey, $macthes)) {
                        if ($macthes[1] != 'hash' && $macthes[1] != '.hash') {
                            if ($sval == 1) {
                                array_push($avail_zones, $macthes[1]);
                            }
                            $avail_zones_setting_hash .= "[{$macthes[1]}:{$sval}]";
                        }
                    }
                }

                if (!$DBFarmRole->GetSetting(DBFarmRole::SETTING_BALANCING_HOSTNAME)) {
                    $elb_name = sprintf("scalr-%s-%s", $DBFarm->Hash, rand(100,999));
                    //Creates a new ELB
                    $elb_dns_name = $elb->loadBalancer->create($elb_name, $listenersList, $avail_zones);
                    if (!empty($elb_dns_name)) {
                        $DBFarmRole->SetSetting(DBFarmRole::SETTING_BALANCING_HOSTNAME, $elb_dns_name);
                        $DBFarmRole->SetSetting(DBFarmRole::SETTING_BALANCING_NAME, $elb_name);
                        $DBFarmRole->SetSetting(DBFarmRole::SETTING_BALANCING_AZ_HASH, $avail_zones_setting_hash);
                        $register_servers = true;
                    }
                }

                if ($DBFarmRole->GetSetting(DBFarmRole::SETTING_BALANCING_NAME)) {
                    $healthCheckType = new HealthCheckData();
                    $healthCheckType->target = $newSettings[DBFarmRole::SETTING_BALANCING_HC_TARGET];
                    $healthCheckType->healthyThreshold = $newSettings[DBFarmRole::SETTING_BALANCING_HC_HTH];
                    $healthCheckType->interval = $newSettings[DBFarmRole::SETTING_BALANCING_HC_INTERVAL];
                    $healthCheckType->timeout = $newSettings[DBFarmRole::SETTING_BALANCING_HC_TIMEOUT];
                    $healthCheckType->unhealthyThreshold = $newSettings[DBFarmRole::SETTING_BALANCING_HC_UTH];
                    $hash = md5(serialize(array(
                        $healthCheckType->target,
                        $healthCheckType->healthyThreshold,
                        $healthCheckType->interval,
                        $healthCheckType->timeout,
                        $healthCheckType->unhealthyThreshold,
                    )));

                    if ($elb_name || ($hash != $DBFarmRole->GetSetting(DBFarmRole::SETTING_BALANCING_HC_HASH))) {
                        //Updates current Elb
                        $elb->loadBalancer->configureHealthCheck(
                            $DBFarmRole->GetSetting(DBFarmRole::SETTING_BALANCING_NAME), $healthCheckType
                        );
                        $DBFarmRole->SetSetting(DBFarmRole::SETTING_BALANCING_HC_HASH, $hash);
                    }

                    // Configure AVAIL zones for the LB
                    if (!$elb_name && $avail_zones_setting_hash != $DBFarmRole->GetSetting(DBFarmRole::SETTING_BALANCING_AZ_HASH)) {
                        $lb = $elb->loadBalancer->describe($DBFarmRole->GetSetting(DBFarmRole::SETTING_BALANCING_NAME))->get(0);
                        $add_avail_zones = array();
                        $rem_avail_zones = array();
                        foreach ($newSettings as $skey => $sval) {
                            if (preg_match("/^lb.avail_zone.(.*)?$/", $skey, $m)) {
                                if ($sval == 1 && !in_array($m[1], $lb->availabilityZones)) {
                                    array_push($add_avail_zones, $m[1]);
                                }
                                if ($sval == 0 && in_array($m[1], $lb->availabilityZones)) {
                                    array_push($rem_avail_zones, $m[1]);
                                }
                            }
                        }
                        if (count($add_avail_zones) > 0) {
                            $lb->enableAvailabilityZones($add_avail_zones);
                        }
                        if (count($rem_avail_zones) > 0) {
                            $lb->disableAvailabilityZones($rem_avail_zones);
                        }
                        $DBFarmRole->SetSetting(DBFarmRole::SETTING_BALANCING_AZ_HASH, $avail_zones_setting_hash);
                    }
                }

                if ($register_servers) {
                    $servers = $DBFarmRole->GetServersByFilter(array('status' => SERVER_STATUS::RUNNING));
                    $instances = array();
                    foreach ($servers as $DBServer) {
                        $instances[] = $DBServer->GetProperty(EC2_SERVER_PROPERTIES::INSTANCE_ID);
                    }
                    if (count($instances) > 0) {
                        $elb->loadBalancer->registerInstances($elb_name, $instances);
                    }
                }
            } else {
                if ($oldSettings[DBFarmRole::SETTING_BALANCING_HOSTNAME]) {

                    try {
                        $elb->loadBalancer->delete($DBFarmRole->GetSetting(DBFarmRole::SETTING_BALANCING_NAME));
                    } catch (Exception $e) {
                    }

                    $DBFarmRole->SetSetting(DBFarmRole::SETTING_BALANCING_NAME, "");
                    $DBFarmRole->SetSetting(DBFarmRole::SETTING_BALANCING_HOSTNAME, "");
                    $DBFarmRole->SetSetting(DBFarmRole::SETTING_BALANCING_USE_ELB, "0");
                    $DBFarmRole->SetSetting(DBFarmRole::SETTING_BALANCING_HC_HASH, "");
                    $DBFarmRole->ClearSettings("lb.avail_zone");
                    $DBFarmRole->ClearSettings("lb.healthcheck");
                    $DBFarmRole->ClearSettings("lb.role.listener");
                }
            }
        } catch (Exception $e) {
            throw new Exception("Error with ELB on Role '{$DBFarmRole->GetRoleObject()->name}': {$e->getMessage()}");
        }
        */
    }
}
