<?php

namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;
use \DateTime;

/**
 * KeyPairData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    28.01.2013
 */
class KeyPairData extends AbstractEc2DataType
{
    /**
     * The key pair name you provided.
     * @var string
     */
    public $keyName;

    /**
     * A SHA-1 digest of the DER encoded private key.
     * @var string
     */
    public $keyFingerprint;

    /**
     * An unencrypted PEM encoded RSA private key.
     * @var string
     */
    public $keyMaterial;

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\Ec2.AbstractEc2DataType::throwExceptionIfNotInitialized()
     */
    protected function throwExceptionIfNotInitialized()
    {
        parent::throwExceptionIfNotInitialized();
        if ($this->keyName === null) {
            throw new Ec2Exception(sprintf(
                'keyName property has not been initialized for the class "%s" yet', get_class($this)
            ));
        }
    }

    /**
     * DeleteKeyPair action
     *
     * Deletes the specified key pair, by removing the public key from Amazon EC2.You must own the key pair
     *
     * @return  bool         Returns true on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function delete()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->keyPair->delete($this->keyName);
    }
}