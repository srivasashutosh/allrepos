<?php
namespace Scalr\Service\Aws\CloudFront;

use Scalr\Service\Aws;
use Scalr\Service\Aws\DataType\ListDataType;

/**
 * AbstractCloudFrontListDataType
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     01.02.2013
 *
 * @method    \Scalr\Service\Aws\CloudFront   getCloudFront()  getCloudFront() Gets an Amazon CloudFront instance.
 * @method    AbstractCloudFrontListDataType  setCloudFront()  setCloudFront(\Scalr\Service\Aws\CloudFront $cloudFront) Sets an Amazon CloudFront instance.
 */
abstract class AbstractCloudFrontListDataType extends ListDataType
{

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.AbstractDataType::getServiceNames()
     */
    public function getServiceNames()
    {
        return array(Aws::SERVICE_INTERFACE_CLOUD_FRONT);
    }
}