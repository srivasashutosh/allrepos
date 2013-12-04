<?php

namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;
use \DateTime;

/**
 * AWS Ec2 ReservedInstanceData (DescribeReservedInstancesResponseSetItemType)
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    14.01.2013
 *
 * @property \Scalr\Service\Aws\Ec2\DataType\ResourceTagSetList      $tagSet            Any tags assigned to the resource
 * @property \Scalr\Service\Aws\Ec2\DataType\RecurringChargesSetList $recurringCharges  The recurring charge tag assigned to the resource.
 */
class ReservedInstanceData extends AbstractEc2DataType
{

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array('tagSet', 'recurringCharges');

    /**
     * The Id of reserved instance
     * @var string
     */
    public $reservedInstancesId;

    /**
     * The instance type on which the Reserved Instance can be used.
     * @var string
     */
    public $instanceType;

    /**
     * The Availability Zone in which the Reserved Instance can be used.
     * @var string
     */
    public $availabilityZone;

    /**
     * The date and time the Reserved Instance started.
     * @var DateTime
     */
    public $start;

    /**
     * The duration of the Reserved Instance, in seconds.
     * @var numeric
     */
    public $duration;

    /**
     * The purchase price of the Reserved Instance
     * @var float
     */
    public $fixedPrice;

    /**
     * The usage price of the Reserved Instance, per hour.
     * @var float
     */
    public $usagePrice;

    /**
     * The number of Reserved Instances purchased.
     * @var int
     */
    public $instanceCount;

    /**
     * The Reserved Instance description
     * Linux/UNIX | Linux/UNIX (Amazon VPC) | Windows| Windows (Amazon VPC)
     * @var string
     */
    public $productDescription;

    /**
     * The state of the Reserved Instance purchase.
     * payment-pending | active | payment-failed | retired
     * @var string
     */
    public $state;

    /**
     * The tenancy of the reserved instance.
     * @var string
     */
    public $instanceTenancy;

    /**
     * The currency of the Reserved Instance. It's specified using ISO 4217
     * standard currency codes.
     * Valid values: As specified in ISO 4217 (e.g., USD, JPY)
     * @var string
     */
    public $currencyCode;

    /**
     * The Reserved Instance offering type.
     * @var string
     */
    public $offeringType;

    public function throwExceptionIfNotInitialized()
    {
        parent::throwExceptionIfNotInitialized();
        if ($this->reservedInstancesId === null) {
            throw new Ec2Exception(sprintf(
                'reservedInstancesId property has not been initialized for the class "%s" yet.',
                get_class($this)
            ));
        }
    }

    /**
     * CreateTags action
     *
     * Adds or overwrites one or more tags for the specified EC2 resource or resources. Each resource can
     * have a maximum of 10 tags. Each tag consists of a key and optional value. Tag keys must be unique per
     * resource.
     *
     * @param   ResourceTagSetList|ResourceTagSetData|array $tagList The key/value pair list of the Tags.
     * @return  bool               Returns true on success or throws an exception otherwise
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function createTags($tagList)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->tag->create($this->reservedInstancesId, $tagList);
    }

    /**
     * DeleteTags action
     *
     * Deletes a specific set of tags from a specific set of resources. This call is designed to follow a
     * DescribeTags call. You first determine what tags a resource has, and then you call DeleteTags with
     * the resource ID and the specific tags you want to delete.
     *
     * @param   ResourceTagSetList|ResourceTagSetData|array $tagList The key/value pair list of the Tags.
     * @return  bool               Returns true on success or throws an exception otherwise
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function deleteTags($tagList)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->tag->delete($this->reservedInstancesId, $tagList);
    }
}