<?php
namespace Scalr\Service\Aws\Rds\Handler;

use Scalr\Service\Aws\Rds\DataType\DescribeEventRequestData;
use Scalr\Service\Aws\Rds\DataType\EventList;
use Scalr\Service\Aws\DataType\ListDataType;
use Scalr\Service\Aws\Client\ClientException;
use Scalr\Service\Aws\DataType\ErrorData;
use Scalr\Service\Aws\RdsException;
use Scalr\Service\Aws\Rds\AbstractRdsHandler;

/**
 * Amazon RDS EventHandler
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     27.03.2013
 */
class EventHandler extends AbstractRdsHandler
{

    /**
     * DescribeEvents action
     *
     * Returns events related to DB instances, DB security groups, DB Snapshots, and DB parameter groups
     * for the past 14 days. Events specific to a particular DB Iinstance, DB security group, DB Snapshot, or
     * DB parameter group can be obtained by providing the source identifier as a parameter. By default, the
     * past hour of events are returned.
     *
     * @param   DescribeEventRequestData $request optional Request object.
     * @return  EventList Returns EventList on success or throws an exception.
     * @throws  ClientException
     * @throws  RdsException
     */
    public function describe(DescribeEventRequestData $request = null)
    {
        return $this->getRds()->getApiHandler()->describeEvents($request);
    }
}