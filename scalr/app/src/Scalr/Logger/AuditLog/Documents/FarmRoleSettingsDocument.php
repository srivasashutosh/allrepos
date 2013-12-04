<?php

namespace Scalr\Logger\AuditLog\Documents;

/**
 * FarmRole Settings document
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    19.02.2013
 */
class FarmRoleSettingsDocument extends AbstractAuditLogDocument
{

    /**
     * ID of the farm
     * @var int
     */
    public $farmroleid;

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
        $ret['farmroleid'] = array(
            'idx' => true,
        );
        return $ret;
    }
}