<?php
namespace Scalr\Service\Aws\CloudWatch;

use Scalr\Service\Aws;
use Scalr\Service\Aws\DataType\ListDataType;

/**
 * AbstractCloudWatchListDataType
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     24.10.2012
 * @method    \Scalr\Service\Aws\CloudWatch   getCloudWatch()  getCloudWatch()                                          Gets an Amazon CloudWatch instance.
 * @method    AbstractCloudWatchListDataType  setCloudWatch()  setCloudWatch(\Scalr\Service\Aws\CloudWatch $cloudWatch) Sets an Amazon CloudWatch instance.
 */
abstract class AbstractCloudWatchListDataType extends ListDataType
{

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.AbstractDataType::getServiceNames()
     */
    public function getServiceNames()
    {
        return array(Aws::SERVICE_INTERFACE_CLOUD_WATCH);
    }
}