<?php

use Scalr\Service\Aws\Ec2\DataType\CreateVolumeRequestData;

class Modules_Platforms_Ec2_Observers_Ebs extends EventObserver
{

    public $ObserverName = 'Elastic Block Storage';

    function __construct()
    {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     * @see EventObserver::OnBeforeInstanceLaunch()
     */
    public function OnBeforeInstanceLaunch(BeforeInstanceLaunchEvent $event)
    {
        if ($event->DBServer->platform != SERVER_PLATFORMS::EC2) {
            return;
        }
        $DBFarm = $event->DBServer->GetFarmObject();
        $DBFarmRole = $event->DBServer->GetFarmRoleObject();

        // Create EBS volume for MySQLEBS
        if (!$event->DBServer->IsSupported("0.6")) {
            // Only for old AMIs
            if ($DBFarmRole->GetRoleObject()->hasBehavior(ROLE_BEHAVIORS::MYSQL) &&
                $DBFarmRole->GetSetting(DBFarmRole::SETTING_MYSQL_DATA_STORAGE_ENGINE) == MYSQL_STORAGE_ENGINE::EBS) {

                $server = $event->DBServer;
                $masterServer = $DBFarm->GetMySQLInstances(true);
                $isMaster = !$masterServer || $masterServer[0]->serverId == $server->serverId;
                $farmMasterVolId = $DBFarmRole->GetSetting(DBFarmRole::SETTING_MYSQL_MASTER_EBS_VOLUME_ID);
                $createEbs = ($isMaster && !$farmMasterVolId);

                if ($createEbs) {
                    Logger::getLogger(LOG_CATEGORY::FARM)->info(
                        new FarmLogMessage($event->DBServer->farmId, sprintf(
                            _("Need EBS volume for MySQL %s instance..."),
                            ($isMaster ? "Master" : "Slave")
                        ))
                    );

                    $req = new CreateVolumeRequestData(
                        $event->DBServer->GetProperty(EC2_SERVER_PROPERTIES::AVAIL_ZONE),
                        $DBFarmRole->GetSetting(DBFarmRole::SETTING_MYSQL_EBS_VOLUME_SIZE)
                    );
                    $aws = $event->DBServer->GetEnvironmentObject()->aws($DBFarmRole->CloudLocation);
                    $res = $aws->ec2->volume->create($req);

                    if (!empty($res->volumeId)) {
                        $DBFarmRole->SetSetting(DBFarmRole::SETTING_MYSQL_MASTER_EBS_VOLUME_ID, $res->volumeId);
                        Logger::getLogger(LOG_CATEGORY::FARM)->info(
                            new FarmLogMessage($event->DBServer->farmId, sprintf(
                                _("MySQL %S volume created. Volume ID: %s..."),
                                ($isMaster ? "Master" : "Slave"),
                                $res->volumeId
                            ))
                        );
                    }
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     * @see EventObserver::OnFarmTerminated()
     */
    public function OnFarmTerminated(FarmTerminatedEvent $event)
    {
        $this->Logger->info("Keep EBS volumes: {$event->KeepEBS}");
        if ($event->KeepEBS == 1) {
            return;
        }
        $this->DB->Execute("UPDATE ec2_ebs SET attachment_status=? WHERE farm_id=? AND ismanual='0'", array(
            EC2_EBS_ATTACH_STATUS::DELETING,
            $this->FarmID
        ));
    }

    /**
     * {@inheritdoc}
     * @see EventObserver::OnEBSVolumeAttached()
     */
    public function OnEBSVolumeAttached(EBSVolumeAttachedEvent $event)
    {
        if ($event->DeviceName) {
            try {
                $DBEBSVolume = DBEBSVolume::loadByVolumeId($event->VolumeID);

                $DBEBSVolume->serverId = $event->DBServer->serverId;
                $DBEBSVolume->deviceName = $event->DeviceName;
                $DBEBSVolume->attachmentStatus = EC2_EBS_ATTACH_STATUS::ATTACHED;
                //$DBEBSVolume->isFsExists = 1;

                $DBEBSVolume->save();
            } catch (Exception $e) {
            }
        }
    }

    /**
     * {@inheritdoc}
     * @see EventObserver::OnEBSVolumeMounted()
     */
    public function OnEBSVolumeMounted(EBSVolumeMountedEvent $event)
    {
        $DBEBSVolume = DBEBSVolume::loadByVolumeId($event->VolumeID);

        $DBEBSVolume->mountStatus = EC2_EBS_MOUNT_STATUS::MOUNTED;
        $DBEBSVolume->deviceName = $event->DeviceName;
        $DBEBSVolume->isFsExists = 1;

        $DBEBSVolume->save();
    }

    /**
     * {@inheritdoc}
     * @see EventObserver::OnHostUp()
     */
    public function OnHostUp(HostUpEvent $event)
    {
        if ($event->DBServer->platform != SERVER_PLATFORMS::EC2) return;
        // Scalarizr will attach and mount volumes by itself
        if ($event->DBServer->IsSupported("0.7.36")) {
            return;
        }

        $volumes = $this->DB->GetAll("SELECT volume_id FROM ec2_ebs WHERE farm_roleid=? AND server_index=?", array(
            $event->DBServer->farmRoleId,
            $event->DBServer->index
        ));

        $this->Logger->info(new FarmLogMessage($this->FarmID, sprintf(
            _("Found %s volumes for server: %s"), count($volumes), $event->DBServer->serverId
        )));

        foreach ($volumes as $volume) {
            if ($volume['volume_id']) {

                $this->Logger->info(new FarmLogMessage($this->FarmID, sprintf(
                    _("Preparing volume #%s for attaching to server: %s."),
                    $volume['volume_id'], $event->DBServer->serverId
                )));

                try {
                    $DBEBSVolume = DBEBSVolume::loadByVolumeId($volume['volume_id']);

                    $DBEBSVolume->serverId = $event->DBServer->serverId;
                    $DBEBSVolume->attachmentStatus = EC2_EBS_ATTACH_STATUS::ATTACHING;
                    $DBEBSVolume->mountStatus = ($DBEBSVolume->mount) ?
                        EC2_EBS_MOUNT_STATUS::AWAITING_ATTACHMENT : EC2_EBS_MOUNT_STATUS::NOT_MOUNTED;

                    $DBEBSVolume->save();
                } catch (Exception $e) {
                    $this->Logger->fatal($e->getMessage());
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     * @see EventObserver::OnHostInit()
     */
    public function OnHostInit(HostInitEvent $event)
    {
        if ($event->DBServer->platform != SERVER_PLATFORMS::EC2) {
            return;
        }

        $DBFarmRole = $event->DBServer->GetFarmRoleObject();

        if ($DBFarmRole->GetSetting(DBFarmRole::SETTING_AWS_USE_EBS)) {
            if (!$this->DB->GetOne("
                    SELECT id FROM ec2_ebs
                    WHERE farm_roleid=? AND server_index=? AND ismanual='0'
                ", array(
                    $event->DBServer->farmRoleId,
                    $event->DBServer->index
                ))) {

                if (in_array($DBFarmRole->GetSetting(DBFarmRole::SETTING_AWS_EBS_TYPE), array('standard','io1'))) {
                    $type = $DBFarmRole->GetSetting(DBFarmRole::SETTING_AWS_EBS_TYPE);
                } else {
                    $type = 'standard';
                }

                $DBEBSVolume = new DBEBSVolume();
                $DBEBSVolume->attachmentStatus = EC2_EBS_ATTACH_STATUS::CREATING;
                $DBEBSVolume->isManual = 0;
                $DBEBSVolume->ec2AvailZone = $DBFarmRole->GetSetting(DBFarmRole::SETTING_AWS_AVAIL_ZONE);
                $DBEBSVolume->ec2Region = $event->DBServer->GetProperty(EC2_SERVER_PROPERTIES::REGION);
                $DBEBSVolume->farmId = $DBFarmRole->FarmID;
                $DBEBSVolume->farmRoleId = $DBFarmRole->ID;
                $DBEBSVolume->serverId = $event->DBServer->serverId;
                $DBEBSVolume->serverIndex = $event->DBServer->index;
                $DBEBSVolume->size = $DBFarmRole->GetSetting(DBFarmRole::SETTING_AWS_EBS_SIZE);
                $DBEBSVolume->type = $type;
                $DBEBSVolume->iops = $DBFarmRole->GetSetting(DBFarmRole::SETTING_AWS_EBS_IOPS);
                $DBEBSVolume->snapId = $DBFarmRole->GetSetting(DBFarmRole::SETTING_AWS_EBS_SNAPID);
                $DBEBSVolume->isFsExists = ($DBFarmRole->GetSetting(DBFarmRole::SETTING_AWS_EBS_SNAPID)) ? 1 : 0;
                $DBEBSVolume->mount = $DBFarmRole->GetSetting(DBFarmRole::SETTING_AWS_EBS_MOUNT);
                $DBEBSVolume->mountPoint = $DBFarmRole->GetSetting(DBFarmRole::SETTING_AWS_EBS_MOUNTPOINT);
                $DBEBSVolume->mountStatus = ($DBFarmRole->GetSetting(DBFarmRole::SETTING_AWS_EBS_MOUNT)) ?
                    EC2_EBS_MOUNT_STATUS::AWAITING_ATTACHMENT : EC2_EBS_MOUNT_STATUS::NOT_MOUNTED;
                $DBEBSVolume->clientId = $event->DBServer->GetFarmObject()->ClientID;
                $DBEBSVolume->envId = $event->DBServer->envId;

                $DBEBSVolume->Save();
            }
        }
    }

    /**
     * {@inheritdoc}
     * @see EventObserver::OnHostDown()
     */
    public function OnHostDown(HostDownEvent $event)
    {
        if ($event->DBServer->platform != SERVER_PLATFORMS::EC2) {
            return;
        }
        if ($event->DBServer->IsRebooting()) {
            return;
        }
        $this->DB->Execute("
            UPDATE ec2_ebs
            SET attachment_status=?,
                mount_status=?,
                device='',
                server_id=''
            WHERE server_id=? AND attachment_status != ?
        ", array(
            EC2_EBS_ATTACH_STATUS::AVAILABLE,
            EC2_EBS_MOUNT_STATUS::NOT_MOUNTED,
            $event->DBServer->serverId,
            EC2_EBS_ATTACH_STATUS::CREATING
        ));
    }
}
