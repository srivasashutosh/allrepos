<?php
namespace Scalr\Service\Aws\Sqs;

use Scalr\Service\Aws;
use Scalr\Service\Aws\AbstractHandler;

/**
 * AbstractSqsHandler
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     06.11.2012
 * @property  \Scalr\Service\Aws\Sqs   $sqs      An Amazon SQS instance
 * @method    \Scalr\Service\Aws\Sqs   getSqs()  getSqs()                            Gets an Amazon SQS instance.
 * @method    AbstractSqsHandler       setSqs()  setSqs(\Scalr\Service\Aws\Sqs $sqs) Sets an Amazon Sqs instance.
 * @method    void                            __constructor()  __constructor(\Scalr\Service\Aws\Sqs $sqs)
 */
abstract class AbstractSqsHandler extends AbstractHandler
{

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.AbstractHandler::getServiceNames()
     */
    public function getServiceNames()
    {
        return array(Aws::SERVICE_INTERFACE_SQS);
    }
}