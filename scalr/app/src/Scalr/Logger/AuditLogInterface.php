<?php

namespace Scalr\Logger;

use Scalr\Logger\AuditLog\KeyValueRecord;
use Scalr\Logger\AuditLog\Exception\AuditLogException;
use Scalr\Logger\AuditLog\AuditLogTags;
use Scalr\Logger\AuditLog\Documents\AbstractAuditLogDocument;
use LoggerStorageInterface;

/**
 * AuditLog interface.
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    31.10.2012
 */
interface AuditLogInterface
{
    /**
     * Gets an array of default options.
     *
     * @return   array Returns an array of the default options.
     */
    public function getDefaultOptions();

    /**
     * Gets an array of the required options
     *
     * @return  array Returns an array of the required options
     */
    public function getRequiredOptions();

    /**
     * Gets an user instance
     *
     * @return \Scalr_Account_User Returns scalr account user
     */
    public function getUser();

    /**
     * Creates log record.
     *
     * If AuditLog isn't enabled this will return success
     * without persisting the record in database.
     * If database connection fails it will persist record for the php log.
     *
     * @param   string                   $message  A message.
     * @param   array|AuditLogTags       $tags     A tags set.
     * @param   object                   $object   optional An object that should be provided for the log record.
     * @return  bool Returns true if record successfully created
     * @throws  AuditLogException
     */
    public function log($message, $tags, $object = null);

    /**
     * Finds records by given criteria.
     *
     * @param   array $criteria Query criteria
     * @param   array $order    Order
     * @param   int   $limit    Limit
     * @return  array Returns array of the LogRecord obejcts
     */
    public function find(array $criteria, array $order, $limit);

    /**
     * Checks whether audit log is enabled.
     *
     *  @return bool Returns TRUE if audit log is enabled or FALSE otherwise.
     */
    public function isEnabled();

    /**
     * Compares two states of the same object and creates KeyValueRecord document that contains a differences.
     *
     * @param   AbstractAuditLogDocument $objectBefore
     * @param   AbstractAuditLogDocument $object
     * @return  KeyValueRecord|null
     */
    public function getKeyValueRecord(AbstractAuditLogDocument $objectBefore, AbstractAuditLogDocument $object);

    /**
     * Gets logger storage instance
     *
     * @return  LoggerStorageInterface Returns logger storage instance
     */
    public function getStorage();
}