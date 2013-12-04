<?php
namespace Scalr\Service\Aws\CloudWatch;

use Scalr\Service\Aws;
use Scalr\Service\Aws\AbstractHandler;

/**
 * AbstractCloudWatchHandler
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     25.10.2012
 * @property  \Scalr\Service\Aws\CloudWatch   $cloudWatch      An Amazon CloudWatch instance
 * @method    \Scalr\Service\Aws\CloudWatch   getCloudWatch()  getCloudWatch()                                          Gets an Amazon CloudWatch instance.
 * @method    AbstractCloudWatchHandler       setCloudWatch()  setCloudWatch(\Scalr\Service\Aws\CloudWatch $cloudWatch) Sets an Amazon CloudWatch instance.
 * @method    void                            __constructor()  __constructor(\Scalr\Service\Aws\CloudWatch $cloudWatch)
 */
abstract class AbstractCloudWatchHandler extends AbstractHandler
{

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.AbstractHandler::getServiceNames()
     */
    public function getServiceNames()
    {
        return array(Aws::SERVICE_INTERFACE_CLOUD_WATCH);
    }
}