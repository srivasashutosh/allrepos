<?php
class Scalr_Db_Backup extends Scalr_Model
{
    protected $dbTableName = 'services_db_backups';
    protected $dbPrimaryKey = "id";
    protected $dbMessageKeyNotFound = "Backup #%s not found in database";

    const STATUS_PENDING_DELETE = 'pending-delete';
    const STATUS_IN_PROGRESS	= 'in-progress';
    const STATUS_AVAILABLE		= 'available';

    protected $dbPropertyMap = array(
        'id'			=> 'id',
        'env_id'		=> 'envId',
        'farm_id'		=> 'farmId',
        'service'		=> 'service',
        'platform'		=> 'platform',
        'provider'		=> 'provider',
        'dtcreated'		=> array('property' => 'dtCreated', 'update' => false, 'type' => 'datetime', 'createSql' => 'NOW()'),
        'size'			=> 'size',
        'status'		=> 'status',
        'cloud_location'=> 'cloudLocation'
    );

    public $id,
        $envId,
        $farmId,
        $service,
        $platform,
        $provider,
        $size,
        $dtCreated,
        $cloudLocation,
        $status;

    private $isPartsChanged;
    private $parts = array();

    public function getParts()
    {
        return $this->db->GetAll('SELECT seq_number as number, size, path FROM services_db_backup_parts WHERE backup_id = ?', array($this->id));
    }

    /**
     * {@inheritdoc}
     * @see Scalr_Model::delete()
     */
    public function delete($id = null)
    {
        $this->status = self::STATUS_PENDING_DELETE;
        $this->save();
    }

    public function addPart($path, $size)
    {
        if ($this->id)
            throw new Exception("Backup part can be added ONLY for new backups");

        $this->isPartsChanged = true;
        $this->parts[] = array('path' => $path, 'size' => $size);
    }

    /**
     * {@inheritdoc}
     * @see Scalr_Model::save()
     */
    public function save($forceInsert = false)
    {
        parent::save($forceInsert);

        // Save parts
        if ($this->isPartsChanged && $this->id) {
            $this->db->Execute("DELETE FROM services_db_backup_parts WHERE backup_id = ?", array($this->id));
            foreach ($this->parts as $n => $part) {
                $this->db->Execute("INSERT INTO services_db_backup_parts SET
                    `backup_id` 	= ?,
                    `path`			= ?,
                    `size`			= ?,
                    `seq_number`	= ?
                ", array($this->id, $part['path'], $part['size'], $n+1));
            }
        }

        return $this;
    }
}

?>