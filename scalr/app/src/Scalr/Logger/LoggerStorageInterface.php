<?php

namespace Scalr\Logger;

use Scalr\Logger\AuditLog\LogRecord;

/**
 * Logger Storage Interface
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    01.11.2012
 */
interface LoggerStorageInterface
{

    /**
     * Finds the records by given options
     *
     * @return array Returns found tokens
     */

    /**
     * Finds log records for the given criteria
     *
     * @param   array      $criteria Criteria array
     * @param   array      $order    Order
     * @param   int        $limit    Limit
     * @return  array      Returns array of the records
     */
    public function find(array $criteria, array $order, $limit);

    /**
     * Writes record to database
     *
     * @param   LogRecord $record A log record
     * @return  bool Returns true if record successfuly saved or false otherwise.
     */
    public function write(LogRecord $record);

    /**
     * Removes all records from the storage
     */
    public function truncate();

    /**
     * Cleanups not relevant records.
     *
     * This helps to rotate log.
     */
    public function cleanup();

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
}