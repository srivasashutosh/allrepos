<?php

class Modules_Platforms_Ec2_Observers_Ec2 extends EventObserver
{

    public $ObserverName = 'EC2';

    function __construct()
    {
        parent::__construct();
    }

    public function OnHostInit(HostInitEvent $event) {

        if ($event->DBServer->platform != SERVER_PLATFORMS::EC2) {
            return;
        }

        try {
            $dbServer = $event->DBServer;
            if ($dbServer->farmId != 0) {
                $ind = $dbServer->GetEnvironmentObject()->aws($dbServer)
                    ->ec2->instance->describe($dbServer->GetProperty(EC2_SERVER_PROPERTIES::INSTANCE_ID))
                    ->get(0)->instancesSet->get(0);

                $nameFormat = $dbServer->GetFarmRoleObject()->GetSetting(DBFarmRole::SETTING_AWS_INSTANCE_NAME_FORMAT);
                if (!$nameFormat)
                    $nameFormat = "%farm_name% -> %role_name% #%instance_index%";

                $params = $dbServer->GetScriptingVars();
                $keys = array_keys($params);
                $f = create_function('$item', 'return "%".$item."%";');
                $keys = array_map($f, $keys);
                $values = array_values($params);

                $instanceName = str_replace($keys, $values, $nameFormat);

                $tags = array(
                    array('key' => "scalr-env-id", 'value' => $dbServer->envId),
                    array('key' => "scalr-owner", 'value' => $dbServer->GetFarmObject()->createdByUserEmail),
                    array('key' => "scalr-farm-id", 'value' => $dbServer->farmId),
                    array('key' => "scalr-farm-name", 'value' => $dbServer->GetFarmObject()->Name),
                    array('key' => "scalr-farm-role-id", 'value' => $dbServer->farmRoleId),
                    array('key' => "scalr-role-name", 'value' => $dbServer->GetFarmRoleObject()->GetRoleObject()->name),
                    array('key' => "scalr-server-id", 'value' => $dbServer->serverId),
                    array('key' => "Name", 'value' => $instanceName)
                );

                //Custom tags
                $cTags = $dbServer->GetFarmRoleObject()->GetSetting(DBFarmRole::SETTING_AWS_TAGS_LIST);
                $tagsList = @explode("\n", $cTags);
                foreach ((array)$tagsList as $tag) {
                    $tag = trim($tag);
                    if ($tag) {
                        $tagChunks = explode("=", $tag);
                        $tags[] = array('key' => trim($tagChunks[0]), 'value' => trim($tagChunks[1]));
                    }
                }

                $res = $ind->createTags($tags);
            }
        } catch (Exception $e) {
            Logger::getLogger('EC2')->fatal("TAGS2: {$e->getMessage()}");
        }
    }
}
