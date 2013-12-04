<?php

namespace Scalr\Logger\AuditLog\Documents;

/**
 * Farm Settings document
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    19.02.2013
 */
class FarmSettingsDocument extends AbstractAuditLogDocument
{

    /**
     * ID of the farm
     * @var int
     */
    public $farmid;

    /**
     * Gets properties for the current document.
     *
     * It can be reflection propreties as well as magic proprties
     *
     * @return   array Returns array of the available propreties
     */
    public static function getDocumentProperties()
    {
        static $ret = null;
        if (!isset($ret)) {
            $ret = array_merge(parent::getDocumentProperties(), array(
                'crypto.key', 'szr.upd.repository', 'szr.upd.schedule'
            ));
            $ret['farmid'] = array(
                'idx' => true,
            );
        }
        return $ret;
    }
}