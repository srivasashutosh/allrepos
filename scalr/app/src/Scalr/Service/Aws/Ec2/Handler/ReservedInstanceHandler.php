<?php
namespace Scalr\Service\Aws\Ec2\Handler;

use Scalr\Service\Aws\Ec2\DataType\ReservedInstanceList;
use Scalr\Service\Aws\Ec2\DataType\OfferingType;
use Scalr\Service\Aws\Ec2\DataType\ReservedInstanceFilterList;
use Scalr\Service\Aws\Ec2\DataType\ReservedInstanceData;
use Scalr\Service\Aws\DataType\ListDataType;
use Scalr\Service\Aws\Client\ClientException;
use Scalr\Service\Aws\DataType\ErrorData;
use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2Handler;

/**
 * ReservedInstanceHandler
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     15.01.2013
 */
class ReservedInstanceHandler extends AbstractEc2Handler
{

    /**
     * Gets ReservedInstanceData object from the EntityManager.
     * You should be aware of the fact that the entity manager is turned off by default.
     *
     * @param   string  $reservedInstancesId An unique Identifier.
     * @return  \Scalr\Service\Aws\Ec2\DataType\ReservedInstanceData|null    Returns ReservedInstanceData if it does exist in the cache or NULL otherwise.
     */
    public function get($reservedInstancesId)
    {
        return $this->getEc2()->getEntityManager()->getRepository('Ec2:ReservedInstance')->find($reservedInstancesId);
    }

    /**
     * DescribeReservedInstances action
     *
     * Describes one or more of the Reserved Instances that you purchased.
     * Starting with the 2011-11-01 API version, AWS expanded its offering of Amazon EC2 Reserved Instances
     * to address a range of projected instance use. There are three types of Reserved Instances based on
     * customer utilization levels: Heavy Utilization, Medium Utilization, and Light Utilization.You determine the
     * type of the Reserved Instances offerings by including the optional offeringType parameter.The Medium
     * Utilization offering type is equivalent to the Reserved Instance offering available before API version
     * 2011-11-01. If you are using tools that predate the 2011-11-01 API version, you only have access to the
     * Medium Utilization Reserved Instance offering type.
     *
     * @param   ListDataType|array|string                                   $reservedInstanceIdList optional One or more instance IDs
     * @param   ReservedInstanceFilterList|ReservedInstanceFilterData|array $filter                 optional A Filter list
     * @return  ReservedInstanceList Returns reserved list of the reserved instances
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function describe($reservedInstanceIdList = null, $filter = null, OfferingType $offefingType = null)
    {
        if ($reservedInstanceIdList !== null && !($reservedInstanceIdList instanceof ListDataType)) {
            $reservedInstanceIdList = new ListDataType($reservedInstanceIdList);
        }
        if ($filter !== null && !($filter instanceof ReservedInstanceFilterList)) {
            $filter = new ReservedInstanceFilterList($filter);
        }
        return $this->getEc2()->getApiHandler()->describeReservedInstances($reservedInstanceIdList, $filter, $offefingType);
    }
}