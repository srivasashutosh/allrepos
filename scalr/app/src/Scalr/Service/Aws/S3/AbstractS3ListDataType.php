<?php
namespace Scalr\Service\Aws\S3;

use Scalr\Service\Aws;
use Scalr\Service\Aws\DataType\ListDataType;

/**
 * AbstractS3ListDataType
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     06.11.2012
 * @method    \Scalr\Service\Aws\S3   getS3()  getS3() Gets an Amazon S3 instance.
 * @method    AbstractS3ListDataType  setS3()  setS3(\Scalr\Service\Aws\S3 $s3) Sets an Amazon S3 instance.
 */
abstract class AbstractS3ListDataType extends ListDataType
{

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.AbstractDataType::getServiceNames()
     */
    public function getServiceNames()
    {
        return array(Aws::SERVICE_INTERFACE_S3);
    }
}