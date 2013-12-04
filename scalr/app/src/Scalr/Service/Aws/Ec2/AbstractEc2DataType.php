<?php
namespace Scalr\Service\Aws\Ec2;

use Scalr\Service\Aws;
use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\AbstractDataType;

/**
 * AbstractEc2DataType
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    26.12.2012
 */
abstract class AbstractEc2DataType extends AbstractDataType
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
     * Throws an exception if this object was not initialized.
     *
     * @throws Ec2Exception
     */
    protected function throwExceptionIfNotInitialized()
    {
        if (!($this->getEc2() instanceof \Scalr\Service\Aws\Ec2)) {
            throw new Ec2Exception(get_class($this) . ' has not been initialized with Ec2 yet.');
        }
    }

    /**
     * Sets Amazon Ec2 service interface instance
     *
     * @param   Aws\Ec2 $ec2 Ec2 service instance
     * @return  AbstractEc2DataType
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