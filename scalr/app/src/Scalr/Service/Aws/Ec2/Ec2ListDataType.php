<?php
namespace Scalr\Service\Aws\Ec2;

use Scalr\Service\Aws;
use Scalr\Service\Aws\DataType\ListDataType;

/**
 * Ec2ListDataType
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     26.12.2012
 */
class Ec2ListDataType extends ListDataType
{

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.AbstractDataType::getServiceNames()
     */
    public function getServiceNames()
    {
        return array(Aws::SERVICE_INTERFACE_EC2);
    }

    /**
     * Sets Amazon Ec2 service interface instance
     *
     * @param   Aws\Ec2 $ec2 Ec2 service instance
     * @return  Ec2ListDataType
     */
    public function setEc2(Aws\Ec2 $ec2 = null)
    {
        $this->_services[Aws::SERVICE_INTERFACE_EC2] = $ec2;
        if ($ec2 !== null) {
            $this->_setServiceRelatedDatasetUpdated(true);
        }
        return $this;
    }

    /**
     * Gets Ec2 service interface instance
     *
     * @return Aws\Ec2 Returns Ec2 service interface instance
     */
    public function getEc2()
    {
        return isset($this->_services[Aws::SERVICE_INTERFACE_EC2]) ? $this->_services[Aws::SERVICE_INTERFACE_EC2] : null;
    }
}