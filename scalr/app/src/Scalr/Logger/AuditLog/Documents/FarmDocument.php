<?php

namespace Scalr\Logger\AuditLog\Documents;

/**
 * Farm document
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    31.10.2012
 */
class FarmDocument extends AbstractAuditLogDocument
{
    /**
     * Farmid
     * @var int
     */
    public $farmid;

    /**
     * Farm name
     * @var string
     */
    public $name;

    /**
     * Environment ID
     *
     * @var int
     */
    public $envid;

    /**
     * ID of Account
     * @var int
     */
    public $clientid;

    /**
     * Launch order
     * @var int
     */
    public $rolesLaunchOrder;

    /**
     * Comments
     * @var string
     */
    public $comments;

    /**
     * Gets a new document by DBFarm object
     *
     * @param   \DBFarm      $dbfarm DBFarm object
     * @return  FarmDocument Returns new FarmDocument
     */
    public static function createFromDBFarm(\DBFarm $dbfarm)
    {
        return self::loadFrom($dbfarm, array(
            'farmid'            => 'ID',
            'name'              => 'Name',
            'envid'             => 'EnvID',
            'clientid'          => 'ClientID',
            'rolesLaunchOrder'  => 'RolesLaunchOrder',
            'comments'          => 'Comments'
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
        $ret['farmid'] = array(
            'idx' => true,
        );
        return $ret;
    }
}