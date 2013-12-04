<?php
namespace Scalr\Service\Aws\Iam;

use Scalr\Service\Aws;
use Scalr\Service\Aws\IamException;
use Scalr\Service\Aws\AbstractDataType;

/**
 * AbstractIamDataType
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    13.11.2012
 * @method   \Scalr\Service\Aws\Iam   getIam()  getIam() Gets an Amazon Iam instance.
 * @method   AbstractIamDataType      setIam()  setIam(\Scalr\Service\Aws\Iam $iam) Sets an Amazon Iam instance.
 */
abstract class AbstractIamDataType extends AbstractDataType
{

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.AbstractDataType::getServiceNames()
     */
    public function getServiceNames()
    {
        return array(Aws::SERVICE_INTERFACE_IAM);
    }

    /**
     * Throws an exception if this object was not initialized.
     *
     * @throws IamException
     */
    protected function throwExceptionIfNotInitialized()
    {
        if (!($this->getIam() instanceof \Scalr\Service\Aws\Iam)) {
            throw new IamException(get_class($this) . ' has not been initialized with Iam yet.');
        }
    }
}