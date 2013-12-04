<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * PropagatingVgwData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    04.04.2013
 */
class PropagatingVgwData extends AbstractEc2DataType
{

    /**
     * The ID of the virtual private gateway (VGW).
     *
     * @var string
     */
    public $gatewayId;

    /**
     * Construct
     *
     * @param   string     $gatewayId The ID of the virtual private gateway (VGW)
     */
    public function __construct($gatewayId = null)
    {
        parent::__construct();
        $this->gatewayId = $gatewayId;
    }
}