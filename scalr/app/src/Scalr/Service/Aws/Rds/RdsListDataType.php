<?php
namespace Scalr\Service\Aws\Rds;

use Scalr\Service\Aws;
use Scalr\Service\Aws\DataType\ListDataType;

/**
 * RdsListDataType
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     07.02.2013
 */
class RdsListDataType extends ListDataType
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
     * Sets Amazon Rds service interface instance
     *
     * @param   Aws\Rds $rds Rds service instance
     * @return  RdsListDataType
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