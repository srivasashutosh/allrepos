<?php
namespace Scalr\Service\Aws\S3;

use Scalr\Service\Aws;
use Scalr\Service\Aws\AbstractHandler;

/**
 * AbstractS3Handler
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     06.11.2012
 * @property  \Scalr\Service\Aws\S3   $s3      An Amazon S3 instance
 * @method    \Scalr\Service\Aws\S3   getS3()  getS3()                            Gets an Amazon S3 instance.
 * @method    AbstractS3Handler       setS3()  setS3(\Scalr\Service\Aws\S3 $s3)   Sets an Amazon S3 instance.
 * @method    void                            __constructor()  __constructor(\Scalr\Service\Aws\S3 $s3)
 */
abstract class AbstractS3Handler extends AbstractHandler
{

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.AbstractHandler::getServiceNames()
     */
    public function getServiceNames()
    {
        return array(Aws::SERVICE_INTERFACE_S3);
    }
}