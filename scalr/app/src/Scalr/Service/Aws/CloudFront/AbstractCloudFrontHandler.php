<?php
namespace Scalr\Service\Aws\CloudFront;

use Scalr\Service\Aws;
use Scalr\Service\Aws\AbstractHandler;

/**
 * AbstractCloudFrontHandler
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     01.02.2013
 *
 * @property  \Scalr\Service\Aws\CloudFront   $cloudFront      An Amazon CloudFront instance
 * @method    \Scalr\Service\Aws\CloudFront   getCloudFront()  getCloudFront()                            Gets an Amazon CloudFront instance.
 * @method    AbstractCloudFrontHandler       setCloudFront()  setCloudFront(\Scalr\Service\Aws\CloudFront $cloudFront)   Sets an Amazon CloudFront instance.
 * @method    void                            __constructor()  __constructor(\Scalr\Service\Aws\CloudFront $cloudFront)
 */
abstract class AbstractCloudFrontHandler extends AbstractHandler
{

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.AbstractHandler::getServiceNames()
     */
    public function getServiceNames()
    {
        return array(Aws::SERVICE_INTERFACE_CLOUD_FRONT);
    }
}