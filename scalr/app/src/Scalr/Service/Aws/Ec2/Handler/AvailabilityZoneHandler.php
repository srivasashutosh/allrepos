<?php
namespace Scalr\Service\Aws\Ec2\Handler;

use Scalr\Service\Aws\DataType\ListDataType;
use Scalr\Service\Aws\Ec2\DataType\AvailabilityZoneList;
use Scalr\Service\Aws\Ec2\DataType\AvailabilityZoneFilterData;
use Scalr\Service\Aws\Ec2\DataType\AvailabilityZoneFilterList;
use Scalr\Service\Aws\Ec2\DataType\AvailabilityZoneData;
use Scalr\Service\Aws\Client\ClientException;
use Scalr\Service\Aws\DataType\ErrorData;
use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2Handler;

/**
 * AvailabilityZoneHandler
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     26.12.2012
 */
class AvailabilityZoneHandler extends AbstractEc2Handler
{

    /**
     * Gets AvailabilityZoneData object from the EntityManager.
     * You should be aware of the fact that the entity manager is turned off by default.
     *
     * @param   string    $zoneName A zone Name.
     * @return  AvailabilityZoneData|null Returns AvailabilityZoneData if it does exist in the cache or NULL otherwise.
     */
    public function get($zoneName)
    {
        return $this->getEc2()->getEntityManager()->getRepository('Ec2:AvailabilityZone')->find($zoneName);
    }

    /**
     * DescribeAvailabilityZones action
     *
     * Describes one or more of the Availability Zones that are currently available to the account. The results
     * include zones only for the region you're currently using.
     *
     * Note! Availability Zones are not the same across accounts.The Availability Zone us-east-1a for account
     * A is not necessarily the same as us-east-1a for account B. Zone assignments are mapped
     * independently for each account.
     *
     * @param   ListDataType|array|string                                   $zoneName optional Zone Name List to filter.
     * @param   AvailabilityZoneFilterList|AvailabilityZoneFilterData|array $filter   optional Filter to apply.
     * @return  AvailabilityZoneList Returns the list of Availability Zones
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function describe($zoneName = null, $filter = null)
    {
        if ($zoneName !== null && !($zoneName instanceof ListDataType)) {
            $zoneName = new ListDataType($zoneName);
        }
        if ($filter !== null && !($filter instanceof AvailabilityZoneFilterList)) {
            $filter = new AvailabilityZoneFilterList($filter);
        }
        return $this->getEc2()->getApiHandler()->describeAvailabilityZones($zoneName, $filter);
    }
}