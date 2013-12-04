<?php
namespace Scalr\Service\Aws\Iam;

use Scalr\Service\Aws;
use Scalr\Service\Aws\AbstractHandler;

/**
 * AbstractIamHandler
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     13.11.2012
 * @property  \Scalr\Service\Aws\Iam   $iam      An Amazon SQS instance
 * @method    \Scalr\Service\Aws\Iam   getIam()  getIam()                              Gets an Amazon Iam instance.
 * @method    AbstractIamHandler       setIam()  setIam(\Scalr\Service\Aws\Iam $iam)   Sets an Amazon Iam instance.
 * @method    void                     __constructor()  __constructor(\Scalr\Service\Aws\Iam $iam)
 */
abstract class AbstractIamHandler extends AbstractHandler
{

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.AbstractHandler::getServiceNames()
     */
    public function getServiceNames()
    {
        return array(Aws::SERVICE_INTERFACE_IAM);
    }
}