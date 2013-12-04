<?php

namespace Scalr\Farm\Role;

class FarmRoleStorageDevice
{
    public
        $farmRoleId,
        $envId,
        $cloudLocation,
        $serverIndex,
        $storageConfigId,
        $config,
        $placement,
        $storageId,
        $status;

    /**
     * @var \ADODB_mysqli
     */
    protected $db;

    const STATUS_ACTIVE = 'active';
    const STATUS_ZOMBY  = 'zomby';

    public function __construct()
    {
        $this->db = \Scalr::getDb();
    }

    /**
     * @param $configId
     * @return Scalr\Farm\Role\FarmRoleStorageDevice[]
     */
    static public function getByConfigId($configId)
    {
        $db = \Scalr::getDb();

        $ids = $db->GetAll("SELECT storage_id, server_index FROM farm_role_storage_devices WHERE storage_config_id = ? AND status = ?",
            array($configId, self::STATUS_ACTIVE)
        );
        if (empty($ids))
            return array();

        $retval = array();
        foreach ($ids as $id) {
            $retval[$id['server_index']] = new self();
            $retval[$id['server_index']]->loadById($id['storage_id']);
        }

        return $retval;
    }

    /**
     * @param $configId
     * @param $serverIndex
     * @return bool|Scalr\Farm\Role\FarmRoleStorageDevice
     */
    static public function getByConfigIdAndIndex($configId, $serverIndex)
    {
        $db = \Scalr::getDb();

        $id = $db->GetOne("SELECT storage_id FROM farm_role_storage_devices WHERE storage_config_id = ? AND server_index = ? AND status = ?",
            array($configId, $serverIndex, self::STATUS_ACTIVE)
        );
        if (!$id)
            return false;

        $device = new self();
        return $device->loadById($id);
    }

    /**
     * @return Scalr\Farm\Role\FarmRoleStorageDevice
     */
    public function loadById($id) {
        $info = $this->db->GetRow("SELECT * FROM farm_role_storage_devices WHERE storage_id = ?", array($id));
        if (!$info)
            return false;

        $this->farmRoleId = $info['farm_role_id'];
        $this->serverIndex = $info['server_index'];
        $this->storageConfigId = $info['storage_config_id'];
        $this->envId = $info['env_id'];
        $this->cloudLocation = $info['cloudLocation'];
        $this->config = @json_decode($info['config']);
        $this->storageId = $info['storage_id'];
        $this->status = $info['status'];
        $this->placement = $info['placement'];

        return $this;
    }

    public function getDisks()
    {
        $retval = array();

        switch ($this->config->type) {
            case FarmRoleStorageConfig::TYPE_EBS:
            case FarmRoleStorageConfig::TYPE_CSVOL:
            case FarmRoleStorageConfig::TYPE_CINDER:
                $retval = array($this->config);
                break;
            case FarmRoleStorageConfig::TYPE_RAID:
                $retval = $this->config->disks;
                break;
        }

        return $retval;
    }

    public function save()
    {
        $this->db->Execute("INSERT INTO farm_role_storage_devices SET
            farm_role_id = ?,
            server_index = ?,
            storage_config_id = ?,
            env_id = ?,
            cloud_location = ?,
            config = ?,
            placement = ?,
            storage_id = ?,
            status = ?
        ON DUPLICATE KEY UPDATE config = ?, status = ?, placement = ?
        ", array(
            $this->farmRoleId,
            $this->serverIndex,
            $this->storageConfigId,
            $this->envId,
            $this->cloudLocation,
            @json_encode($this->config),
            $this->placement,
            $this->storageId,
            $this->status,

            @json_encode($this->config),
            $this->status,
            $this->placement
        ));

        return $this;
    }
}
