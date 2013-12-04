<?php

namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\DataType\ListDataType;
use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * IamInstanceProfileRequestData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    17.01.2013
 */
class IamInstanceProfileRequestData extends AbstractEc2DataType
{

    /**
     * The Amazon resource name (ARN) of the IAM Instance Profile (IIP) to associate with the instance.
     * @var string
     */
    public $arn;

    /**
     * The name of the IAM Instance Profile (IIP) to associate with the instance.
     * @var string
     */
    public $name;

    /**
     * Constructor
     * @param   string       $arn  optional The Amazon resource name (ARN) of the IAM Instance Profile (IIP) to associate with the instance.
     * @param   string       $name optional The name of the IAM Instance Profile (IIP) to associate with the instance.
     */
    public function __construct($arn = null, $name = null)
    {
        parent::__construct();
        $this->arn = $arn;
        $this->name = $name;
    }
}