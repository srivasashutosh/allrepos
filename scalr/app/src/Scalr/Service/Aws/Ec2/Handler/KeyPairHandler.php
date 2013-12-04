<?php
namespace Scalr\Service\Aws\Ec2\Handler;

use Scalr\Service\Aws\Ec2\DataType\KeyPairData;
use Scalr\Service\Aws\Ec2\DataType\KeyPairList;
use Scalr\Service\Aws\DataType\ListDataType;
use Scalr\Service\Aws\Client\ClientException;
use Scalr\Service\Aws\DataType\ErrorData;
use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2Handler;

/**
 * KeyPairHandler
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     28.01.2013
 */
class KeyPairHandler extends AbstractEc2Handler
{

    /**
     * CreateKeyPair action
     *
     * Creates a new 2048-bit RSA key pair with the specified name. The public key is stored by Amazon EC2
     * and the private key is returned to you. The private key is returned as an unencrypted PEM encoded
     * PKCS#8 private key. If a key with the specified name already exists, Amazon EC2 returns an error.
     *
     * Tip! The key pair returned to you works only in the region you're using when you create the key pair.
     * To create a key pair that works in all regions, use ImportKeyPair
     *
     * @param   string       $keyName A unique name for the key pair.
     * @return  KeyPairData  Returns KeyPairData on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function create($keyName)
    {
        return $this->getEc2()->getApiHandler()->createKeyPair($keyName);
    }

    /**
     * DeleteKeyPair action
     *
     * Deletes the specified key pair, by removing the public key from Amazon EC2.You must own the key pair
     *
     * @param   string       $keyName A unique key name for the key pair.
     * @return  bool         Returns true on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function delete($keyName)
    {
        return $this->getEc2()->getApiHandler()->deleteKeyPair($keyName);
    }

    /**
     * DescribeKeyPairs action
     *
     * Describes one or more of your key pairs.
     *
     * @param   ListDataType|array|string $keyNameList  The list of the names
     * @param   array                     $filter       Array of the key => value properties.
     * @return  KeyPairList               Returns KeyPairList on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function describe($keyNameList = null, array $filter = null)
    {
        if ($keyNameList !== null && !($keyNameList instanceof ListDataType)) {
            $keyNameList = new ListDataType($keyNameList);
        }
        return $this->getEc2()->getApiHandler()->describeKeyPairs($keyNameList, $filter);
    }
}