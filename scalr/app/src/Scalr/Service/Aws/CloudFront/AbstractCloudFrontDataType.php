<?php
namespace Scalr\Service\Aws\CloudFront;

use Scalr\Service\Aws;
use Scalr\Service\Aws\CloudFrontException;
use Scalr\Service\Aws\AbstractDataType;

/**
 * AbstractCloudFrontDataType
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    01.02.2012
 * @method   \Scalr\Service\Aws\CloudFront   getCloudFront()  getCloudFront() Gets an Amazon CloudFront instance.
 * @method   AbstractCloudFrontDataType      setCloudFront()  setCloudFront(\Scalr\Service\Aws\CloudFront $cloudFront) Sets an Amazon CloudFront instance.
 */
abstract class AbstractCloudFrontDataType extends AbstractDataType
{

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.AbstractDataType::getServiceNames()
     */
    public function getServiceNames()
    {
        return array(Aws::SERVICE_INTERFACE_CLOUD_FRONT);
    }

    /**
     * Throws an exception if this object was not initialized.
     *
     * @throws CloudFrontException
     */
    protected function throwExceptionIfNotInitialized()
    {
        if (!($this->getCloudFront() instanceof \Scalr\Service\Aws\CloudFront)) {
            throw new CloudFrontException(get_class($this) . ' has not been initialized with CloudFront yet.');
        }
    }
}