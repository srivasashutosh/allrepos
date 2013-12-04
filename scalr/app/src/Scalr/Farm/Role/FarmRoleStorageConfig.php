<?php

namespace Scalr\Farm\Role;

class FarmRoleStorageConfig
{
    public  $id,
            $type,
            $index,
            $fs,
            $reUse,
            $rebuild,
            $mount,
            $mountPoint,
            $status,
            $settings;

    protected   $db,
                $farmRole;

    const TYPE_RAID = 'raid';

    const TYPE_RAID_EBS = 'raid.ebs';
    const TYPE_RAID_CSVOL = 'raid.csvol';
    const TYPE_RAID_CINDER = 'raid.cinder';

    const TYPE_EBS = 'ebs';
    const TYPE_CSVOL = 'csvol';
    const TYPE_CINDER = 'cinder';

    const SETTING_RAID_LEVEL = 'raid.level';
    const SETTING_RAID_VOLUMES_COUNT = 'raid.volumes_count';

    const SETTING_CSVOL_SIZE = 'csvol.size';
    const SETTING_CSVOL_SNAPSHOT = 'csvol.snapshot';

    const SETTING_CINDER_SIZE = 'cinder.size';
    const SETTING_CINDER_SNAPSHOT = 'cinder.snapshot';

    const SETTING_EBS_SIZE = 'ebs.size';
    const SETTING_EBS_TYPE = 'ebs.type';
    const SETTING_EBS_IOPS = 'ebs.iops';
    const SETTING_EBS_SNAPSHOT = 'ebs.snapshot';

    const STATE_PENDING_DELETE = 'Pending delete';
    const STATE_PENDING_CREATE = 'Pending create';
    const STATE_CONFIGURED = 'Configured';

    public function __construct(\DBFarmRole $farmRole)
    {
        $this->farmRole = $farmRole;
        $this->db = \Scalr::getDb();
    }

    /**
     * @param \DBFarmRole $farmRole
     * @return FarmRoleStorageConfig[]
     */
    static public function getByFarmRole(\DBFarmRole $farmRole)
    {
        $db = \Scalr::getDb();
        $configs = array();
        $ids = $db->GetCol('SELECT id FROM farm_role_storage_config WHERE farm_role_id = ?', array($farmRole->ID));
        foreach ($ids as $id) {
            $config = new FarmRoleStorageConfig($farmRole);
            if ($config->loadById($id))
                $configs[] = $config;
        }

        return $configs;
    }

    public function loadById($id)
    {
        $data = $this->db->GetRow('SELECT * FROM farm_role_storage_config WHERE id = ? AND farm_role_id = ?', array($id, $this->farmRole->ID));
        if (empty($data))
            return false;

        $this->id = $data['id'];
        $this->type = $data['type'];
        $this->index = $data['index'];
        $this->fs = $data['fs'];
        $this->reUse = $data['re_use'];
        $this->mount = $data['mount'];
        $this->mountPoint = $data['mountpoint'];
        $this->rebuild = $data['rebuild'];
        $this->status = $data['status'];
        $this->settings = array();
        foreach($this->db->GetAll('SELECT name, value FROM farm_role_storage_settings WHERE storage_config_id = ?', array($this->id)) as $value) {
            $this->settings[$value['name']] = $value['value'];
        }

        return $this;
    }

    public function create(array $config)
    {
        $deleteFlag = false;

        if (!is_array($config) || !is_array($config['settings']))
            return;

        $type = $config['type'];
        $settings = array();

        if (! (in_array($type, array(self::TYPE_RAID_EBS, self::TYPE_RAID_CSVOL, self::TYPE_RAID_CINDER, self::TYPE_EBS, self::TYPE_CSVOL, self::TYPE_CINDER)))) {
            throw new FarmRoleStorageException('[Storage] Invalid type');
        }

        if ($type == self::TYPE_CSVOL || $type == self::TYPE_RAID_CSVOL) {
            $volSize = intval($config['settings'][self::SETTING_CSVOL_SIZE]);

            if ($volSize < 1 || $volSize > 1024)
                throw new FarmRoleStorageException('Volume size should be from 1 to 1024 GB');

            $settings[self::SETTING_CSVOL_SNAPSHOT] = $config['settings'][self::SETTING_CSVOL_SNAPSHOT];
            $settings[self::SETTING_CSVOL_SIZE] = $volSize;
        } elseif ($type == self::TYPE_CINDER || $type == self::TYPE_RAID_CINDER) {
            $volSize = intval($config['settings'][self::SETTING_CINDER_SIZE]);

            if ($volSize < 100 || $volSize > 1024)
                throw new FarmRoleStorageException('Volume size should be from 100 to 1024 GB');

            $settings[self::SETTING_CINDER_SNAPSHOT] = $config['settings'][self::SETTING_CINDER_SNAPSHOT];
            $settings[self::SETTING_CINDER_SIZE] = $volSize;
        } elseif ($type == self::TYPE_EBS || $type == self::TYPE_RAID_EBS) {
            $ebsSize = intval($config['settings'][self::SETTING_EBS_SIZE]);
            $ebsType = $config['settings'][self::SETTING_EBS_TYPE];
            $ebsIops = intval($config['settings'][self::SETTING_EBS_IOPS]);
            $ebsSnapshot = $config['settings'][self::SETTING_EBS_SNAPSHOT];

            if (! in_array($ebsType, array('standard', 'io1')))
                throw new FarmRoleStorageException('EBS type should be standard or iops');

            if ($ebsSize < 1 || $ebsSize > 1024)
                throw new FarmRoleStorageException('EBS size should be from 1 to 1024 GB');

            $settings[self::SETTING_EBS_SIZE] = $ebsSize;
            $settings[self::SETTING_EBS_TYPE] = $ebsType;

            if ($ebsType == 'io1') {
                if ($ebsIops < 100 || $ebsIops > 2000)
                    throw new FarmRoleStorageException('EBS iops should be from 100 to 2000');

                if (($ebsIops / $ebsSize) > 10)
                    throw new FarmRoleStorageException(sprintf('Invalid ratio. You should increase volume size to %d GB or decrease volume iops to %d', (int) $ebsIops/10, $ebsSize * 10));

                $settings[self::SETTING_EBS_IOPS] = $ebsIops;
            }

            $settings[self::SETTING_EBS_SNAPSHOT] = $ebsSnapshot;
        }

        // TODO: validate raid, cvsol
        $settings[self::SETTING_RAID_LEVEL] = $config['settings'][self::SETTING_RAID_LEVEL];
        $settings[self::SETTING_RAID_VOLUMES_COUNT] = $config['settings'][self::SETTING_RAID_VOLUMES_COUNT];

        if ($config['id']) {
            $this->loadById($config['id']);

            if ($this->status == self::STATE_PENDING_CREATE) {
                if ($config['status'] == self::STATE_PENDING_DELETE) {
                    // mark for delete on save
                    $deleteFlag = true;
                } else {
                    $this->type = $config['type'];
                    $this->fs = $config['fs'];
                    $this->reUse = !empty($config['reUse']) ? 1 : NULL;
                    $this->rebuild = !empty($config['rebuild']) ? 1 : NULL;
                    $this->mount = !empty($config['mount']) ? 1 : NULL;
                    $this->mountPoint = $config['mountPoint'];
                }
            } elseif ($config['status'] == self::STATE_PENDING_DELETE) {
                $this->status = self::STATE_PENDING_DELETE;
            }
        } else {
            $this->id = \Scalr::GenerateUID();
            $this->type = $config['type'];
            $this->fs = $config['fs'];
            $this->reUse = !empty($config['reUse']) ? 1 : NULL;
            $this->rebuild = !empty($config['rebuild']) ? 1 : NULL;
            $this->mount = !empty($config['mount']) ? 1 : NULL;
            $this->mountPoint = $config['mountPoint'];
            $this->status = self::STATE_PENDING_CREATE;
        }

        if ($deleteFlag) {
            $this->delete();
            return;
        }

        $this->settings = $settings;
        $this->save();
    }

    public function save()
    {
        $this->db->Execute("INSERT INTO farm_role_storage_config SET
            id = ?,
            farm_role_id = ?,
            `index` = ?,
            `type` = ?,
            fs = ?,
            re_use = ?,
            rebuild = ?,
            mount = ?,
            mountpoint = ?,
            status = ?
        ON DUPLICATE KEY UPDATE `index` = ?, `type` = ?, fs = ?, re_use = ?, `rebuild` = ?, mount = ?, mountpoint = ?, status = ?
        ", array(
            $this->id,
            $this->farmRole->ID,
            $this->index,
            $this->type,
            $this->fs,
            $this->reUse,
            $this->rebuild,
            $this->mount,
            $this->mountPoint,
            $this->status,

            $this->index,
            $this->type,
            $this->fs,
            $this->reUse,
            $this->rebuild,
            $this->mount,
            $this->mountPoint,
            $this->status
        ));

        $this->db->Execute('DELETE FROM farm_role_storage_settings WHERE storage_config_id = ?', array($this->id));

        if (count($this->settings)) {
            $query = array();
            $args = array();
            foreach ($this->settings as $key => $value) {
                $query[] = '(?,?,?)';
                $args[] = $this->id;
                $args[] = $key;
                $args[] = $value;
            }
            $this->db->Execute('INSERT INTO farm_role_storage_settings (storage_config_id, name, value) VALUES ' . implode(',', $query), $args);
        }
    }

    public function delete($id = null)
    {
        $id = !is_null($id) ? $id : $this->id;
        $this->db->Execute('DELETE FROM farm_role_storage_settings WHERE storage_config_id = ?', array($id));

        //TODO: NEET TO SET FarmRoleStorageDevice::TYPE_ZOMBY to all devices of deleted config
        // $this->db->Execute('DELETE FROM farm_role_storage_devices WHERE storage_id = ?', array($id));
        $this->db->Execute('DELETE FROM farm_role_storage_config WHERE id = ?', array($id));
    }
}
