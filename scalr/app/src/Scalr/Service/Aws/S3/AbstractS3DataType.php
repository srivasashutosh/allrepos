<?php
namespace Scalr\Service\Aws\S3;

use Scalr\Service\Aws;
use Scalr\Service\Aws\S3Exception;
use Scalr\Service\Aws\AbstractDataType;

/**
 * AbstractS3DataType
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    06.11.2012
 * @method   \Scalr\Service\Aws\S3   getS3()  getS3() Gets an Amazon S3 instance.
 * @method   AbstractS3DataType      setS3()  setS3(\Scalr\Service\Aws\S3 $s3) Sets an Amazon S3 instance.
 */
abstract class AbstractS3DataType extends AbstractDataType
{

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.AbstractDataType::getServiceNames()
     */
    public function getServiceNames()
    {
        return array(Aws::SERVICE_INTERFACE_S3);
    }

    /**
     * Throws an exception if this object was not initialized.
     *
     * @throws S3Exception
     */
    protected function throwExceptionIfNotInitialized()
    {
        if (!($this->getS3() instanceof \Scalr\Service\Aws\S3)) {
            throw new S3Exception(get_class($this) . ' has not been initialized with S3 yet.');
        }
    }
}