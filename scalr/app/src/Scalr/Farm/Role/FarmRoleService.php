<?php
namespace Scalr\Farm\Role;

class FarmRoleService
{
    protected $farmRole;

    /**
     * @var \ADODB_mysqli
     */
    protected $db;

    protected $serviceId;

    protected $type,
       $farmId,
       $envId,
       $farmRoleId,
       $platform,
       $cloudLocation;

    private $exists = false;


    /* Amazon Services */
    const SERVICE_AWS_ELB = 'aws_elb';
    const SERVICE_AWS_RDS = 'aws_rds';

    /* GCE Services */
    const SERVICE_GCE_LB  = 'gce_lb';

    /**
     * @param integer $envId
     * @param string $serviceId
     * @return \Scalr\Farm\Role\FarmRoleService|boolean
     */
    public static function findFarmRoleService($envId, $serviceId)
    {
        $db = \Scalr::getDb();

        $service = $db->GetRow("SELECT id, farm_role_id FROM farm_role_cloud_services WHERE id = ? AND env_id = ?", array($serviceId, $envId));
        if ($service) {
            try {
                $dbFarmRole = \DBFarmRole::LoadByID($service['farm_role_id']);
                return new self($dbFarmRole, $service['id']);
            } catch (\Exception $e) {
                return false;
            }
        }

        return false;
    }

    public function getFarmRole()
    {
        return $this->farmRole;
    }

    public function getServiceId()
    {
        return $this->serviceId;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type) {
        if (!$this->type)
            $this->type = $type;
        else
            throw new \Exception("Service type already set");
    }

    public function __construct(\DBFarmRole $dbFarmRole, $serviceId)
    {
        $this->db = \Scalr::getDb();
        $this->farmRole = $dbFarmRole;
        $this->serviceId = $serviceId;
        $this->farmRoleId = $this->farmRole->ID;
        $this->serviceId = $serviceId;

        $service = $this->db->GetRow("SELECT * FROM farm_role_cloud_services WHERE id = ? AND farm_role_id = ?", array($serviceId, $this->farmRole->ID));
        if ($service) {
            $this->envId = $service['env_id'];
            $this->farmId = $service['farmId'];
            $this->type = $service['type'];
            $this->platform = $service['platform'];
            $this->cloudLocation = $service['cloud_location'];

            $this->exists = true;
        } else {
            $this->envId = $this->farmRole->GetFarmObject()->EnvID;
            $this->farmId = $this->farmRole->FarmID;
            $this->platform = $this->farmRole->Platform;
            $this->cloudLocation = $this->farmRole->CloudLocation;

            $this->exists = false;
        }
    }

    public function remove() {
        $this->db->Execute("DELETE FROM farm_role_cloud_services WHERE id = ? AND env_id = ?", array(
            $this->serviceId, $this->envId
        ));
    }

    public function save() {
        if ($this->exists) {
            /*
            $this->db->Execute("UPDATE farm_role_cloud_services SET

            ");
            */
        } else {
            $this->db->Execute("INSERT INTO farm_role_cloud_services SET
                `id`      = ?,
                `type`    = ?,
                `env_id`  = ?,
                `farm_id` = ?,
                `farm_role_id` = ?,
                `platform` = ?,
                `cloud_location` = ?
            ", array(
                $this->serviceId,
                $this->type,
                $this->envId,
                $this->farmId,
                $this->farmRoleId,
                $this->platform,
                $this->cloudLocation
            ));
        }
    }
}
