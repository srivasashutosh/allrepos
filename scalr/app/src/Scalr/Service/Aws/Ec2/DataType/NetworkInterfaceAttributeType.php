<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\DataType\StringType;
use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * NetworkInterfaceAttributeType
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    03.04.2013
 */
class NetworkInterfaceAttributeType extends StringType
{

    const ATTR_DESCRIPTION = 'description';

    const ATTR_GROUP_SET = 'groupSet';

    const ATTR_SOURCE_DEST_CHECK = 'sourceDestCheck';

    const ATTR_ATTACHMENT = 'attachment';

    public static function getPrefix()
    {
        return 'ATTR_';
    }
}