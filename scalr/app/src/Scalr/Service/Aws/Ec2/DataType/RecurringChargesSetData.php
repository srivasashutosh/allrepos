<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * RecurringChargesSetData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    14.01.2013
 */
class RecurringChargesSetData extends AbstractEc2DataType
{

    /**
     * The frequency of the recurring charge. (Hourly)
     * @var string
     */
    public $frequency;

    /**
     * The amount of the recurring charge.
     * @var float
     */
    public $amount;
}