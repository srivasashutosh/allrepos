<?php

namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * InstanceStatusTypeData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    15.01.2013
 *
 * @property InstanceStatusDetailsSetList     $details   Information about system instance health or application instance health.
 */
class InstanceStatusTypeData extends AbstractEc2DataType
{

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array('details');

    /**
     * The status. Valid values: ok | impaired | insufficient-data | not-applicable
     * @var string
     */
    public $status;
}