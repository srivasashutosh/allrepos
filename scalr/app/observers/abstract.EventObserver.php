<?php

abstract class EventObserver implements IEventObserver
{

    /**
     * Farm ID
     *
     * @var integer
     */
    protected $FarmID;

    /**
     * Logger instance
     *
     * @var Logger
     */
    protected $Logger;

    /**
     * ADODB instance
     *
     * @var \ADODB_mysqli
     */
    protected $DB;

    /**
     * DI Container
     *
     * @var \Scalr\DependencyInjection\Container
     */
    protected $container;

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        $this->container = \Scalr::getContainer();
        $this->DB = \Scalr::getDb();
        $this->Logger = Logger::getLogger(__CLASS__);
    }

    /**
     * Gets DI Container
     *
     * @return \Scalr\DependencyInjection\Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Set FARM ID
     *
     * @param integer $farmid
     */
    public function SetFarmID($farmid)
    {
        $this->FarmID = $farmid;
    }

    public function OnCheckFailed(CheckFailedEvent $event)
    {
    }

    public function OnCheckRecovered(CheckRecoveredEvent $event)
    {
    }

    public function OnHostInit(HostInitEvent $event)
    {
    }

    public function OnHostUp(HostUpEvent $event)
    {
    }

    public function OnHostDown(HostDownEvent $event)
    {
    }

    public function OnHostCrash(HostCrashEvent $event)
    {
        $HostDownEvent = new HostDownEvent($event->DBServer);
        $this->OnHostDown($HostDownEvent);
    }

    public function OnRebundleComplete(RebundleCompleteEvent $event)
    {
    }

    public function OnRebundleFailed(RebundleFailedEvent $event)
    {
    }

    public function OnRebootBegin(RebootBeginEvent $event)
    {
    }

    public function OnRebootComplete(RebootCompleteEvent $event)
    {
    }

    public function OnFarmLaunched(FarmLaunchedEvent $event)
    {
    }

    public function OnFarmTerminated(FarmTerminatedEvent $event)
    {
    }

    public function OnCustomEvent(CustomEvent $event)
    {
    }

    /**
     * @deprecated
     */
    public function OnNewMysqlMasterUp(NewMysqlMasterUpEvent $event)
    {
    }

    public function OnNewDbMsrMasterUp(NewDbMsrMasterUpEvent $event)
    {
    }

    public function OnMysqlBackupComplete(MysqlBackupCompleteEvent $event)
    {
    }

    public function OnMysqlBackupFail(MysqlBackupFailEvent $event)
    {
    }

    public function OnIPAddressChanged(IPAddressChangedEvent $event)
    {
    }

    public function OnMySQLReplicationFail(MySQLReplicationFailEvent $event)
    {
    }

    public function OnMySQLReplicationRecovered(MySQLReplicationRecoveredEvent $event)
    {
    }

    public function OnEBSVolumeMounted(EBSVolumeMountedEvent $event)
    {
    }

    public function OnBeforeInstanceLaunch(BeforeInstanceLaunchEvent $event)
    {
    }

    public function OnBeforeHostTerminate(BeforeHostTerminateEvent $event)
    {
    }

    public function OnDNSZoneUpdated(DNSZoneUpdatedEvent $event)
    {
    }

    public function OnRoleOptionChanged(RoleOptionChangedEvent $event)
    {
    }

    public function OnEBSVolumeAttached(EBSVolumeAttachedEvent $event)
    {
    }

    public function OnServiceConfigurationPresetChanged(ServiceConfigurationPresetChangedEvent $event)
    {
    }

    public function OnBeforeHostUp(BeforeHostUpEvent $event)
    {
    }
}
