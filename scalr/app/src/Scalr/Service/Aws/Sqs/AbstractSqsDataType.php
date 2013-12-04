<?php
namespace Scalr\Service\Aws\Sqs;

use Scalr\Service\Aws;
use Scalr\Service\Aws\SqsException;
use Scalr\Service\Aws\AbstractDataType;

/**
 * AbstractSqsDataType
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    06.11.2012
 * @method   \Scalr\Service\Aws\Sqs   getSqs()  getSqs() Gets an Amazon Sqs instance.
 * @method   AbstractSqsDataType      setSqs()  setSqs(\Scalr\Service\Aws\Sqs $sqs) Sets an Amazon Sqs instance.
 */
abstract class AbstractSqsDataType extends AbstractDataType
{

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.AbstractDataType::getServiceNames()
     */
    public function getServiceNames()
    {
        return array(Aws::SERVICE_INTERFACE_SQS);
    }

    /**
     * Throws an exception if this object was not initialized.
     *
     * @throws SqsException
     */
    protected function throwExceptionIfNotInitialized()
    {
        if (!($this->getSqs() instanceof \Scalr\Service\Aws\Sqs)) {
            throw new SqsException(get_class($this) . ' has not been initialized with Sqs yet.');
        }
    }
}