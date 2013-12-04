<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\DataType\StringType;
use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * KeyPairFilterNameType
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    08.02.2013
 */
class KeyPairFilterNameType extends StringType
{

    /**
     * The fingerprint of the key pair.
     */
    const TYPE_FINGERPRINT = 'fingerprint';

    /**
     * The name of the key pair.
     */
    const TYPE_KEY_NAME = 'key-name';


    public static function getPrefix()
    {
        return 'TYPE_';
    }
}