<?php

namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * ProductCodeSetData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    09.01.2013
 */
class ProductCodeSetData extends AbstractEc2DataType
{

    /**
     * The product code.
     * @var string
     */
    public $productCode;

    /**
     * The type of product code.
     * devpay | marketplace
     * @var string
     */
    public $type;
}