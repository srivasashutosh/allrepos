<?php

namespace Scalr\Logger\AuditLog\Documents;

/**
 * Farm Role document
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    18.02.2013
 */
class FarmRoleDocument extends AbstractAuditLogDocument
{
    /**
     * Farmid
     * @var int
     */
    public $farmid;

    /**
     * ID
     * @var int
     */
    public $id;

    /**
     * ID of the Role
     * @var int
     */
    public $roleid;

    /**
     * ID of the new role
     * @var int
     */
    public $newRoleid;

    /**
     * Platform
     * @var string
     */
    public $platform;

    /**
     * Cloud location
     * @var string
     */
    public $cloudLocation;

    /**
     * Launch index
     * @var int
     */
    public $launchIndex;

    /**
     * Gets a new document by DBFarmRole object
     *
     * @param   \DBFarmRole      $obj DBFarmRole object
     * @return  FarmRoleDocument Returns new FarmDocument
     */
    public static function createFromDBFarmRole(\DBFarmRole $obj)
    {
        return self::loadFrom($obj, array(
            'id'                => 'ID',
            'farmid'            => 'FarmID',
            'roleid'            => 'RoleID',
            'newRoleid'         => 'NewRoleID',
            'platform'          => 'Platform',
            'cloudLocation'     => 'CloudLocation',
            'launchIndex'       => 'LaunchIndex',
        ));
    }

    /**
     * Gets properties for the current document.
     *
     * It can be reflection propreties as well as magic proprties
     *
     * @return   array Returns array of the available propreties
     */
    public static function getDocumentProperties()
    {
        $ret = parent::getDocumentProperties();
        $ret['id'] = array(
            'idx' => true,
        );
        $ret['farmid'] = array(
            'idx' => true,
        );
        $ret['roleid'] = array(
            'idx' => true,
        );
        return $ret;
    }
}