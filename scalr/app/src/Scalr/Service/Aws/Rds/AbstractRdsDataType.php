<?php
namespace Scalr\Service\Aws\Rds;

use Scalr\Service\Aws;
use Scalr\Service\Aws\RdsException;
use Scalr\Service\Aws\AbstractDataType;

/**
 * AbstractRdsDataType
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    07.03.2013
 */
abstract class AbstractRdsDataType extends AbstractDataType
{

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.AbstractDataType::getServiceNames()
     */
    public function getServiceNames()
    {
        return array(Aws::SERVICE_INTERFACE_RDS);
    }

    /**
     * Throws an exception if this object was not initialized.
     *
     * @throws RdsException
     */
    protected function throwExceptionIfNotInitialized()
    {
        if (!($this->getRds() instanceof \Scalr\Service\Aws\Rds)) {
            throw new RdsException(get_class($this) . ' has not been initialized with Rds yet.');
        }
    }

    /**
     * Sets Amazon Rds service interface instance
     *
     * @param   Aws\Rds $rds Rds service instance
     * @return  AbstractRdsDataType
     */
    public function setRds(Aws\Rds $rds = null)
    {
        $this->_services[Aws::SERVICE_INTERFACE_RDS] = $rds;
        if ($rds !== null) {
            $this->_setServiceRelatedDatasetUpdated(true);
        }
        return $this;
    }

    /**
     * Gets Rds service interface instance
     *
     * @return Aws\Rds Returns Rds service interface instance
     */
    public function getRds()
    {
        return isset($this->_services[Aws::SERVICE_INTERFACE_RDS]) ? $this->_services[Aws::SERVICE_INTERFACE_RDS] : null;
    }
}