<?php
namespace Scalr\Service\Aws\CloudWatch;

use Scalr\Service\Aws;
use Scalr\Service\Aws\CloudWatchException;
use Scalr\Service\Aws\AbstractDataType;

/**
 * AbstractCloudWatchDataType
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    24.10.2012
 * @method   \Scalr\Service\Aws\CloudWatch   getCloudWatch()  getCloudWatch()                                          Gets an Amazon CloudWatch instance.
 * @method   AbstractCloudWatchDataType      setCloudWatch()  setCloudWatch(\Scalr\Service\Aws\CloudWatch $cloudWatch) Sets an Amazon CloudWatch instance.
 */
abstract class AbstractCloudWatchDataType extends AbstractDataType
{

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.AbstractDataType::getServiceNames()
     */
    public function getServiceNames()
    {
        return array(Aws::SERVICE_INTERFACE_CLOUD_WATCH);
    }

    /**
     * Throws an exception if this object was not initialized.
     *
     * @throws CloudWatchException
     */
    protected function throwExceptionIfNotInitialized()
    {
        if (!($this->getCloudWatch() instanceof \Scalr\Service\Aws\CloudWatch)) {
            throw new CloudWatchException(get_class($this) . ' has not been initialized with CloudWatch yet.');
        }
    }
}