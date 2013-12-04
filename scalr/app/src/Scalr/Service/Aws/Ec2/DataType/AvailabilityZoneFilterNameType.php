<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\DataType\StringType;
use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * AvailabilityZoneFilterNameType
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    26.12.2012
 */
class AvailabilityZoneFilterNameType extends StringType
{
    const TYPE_MESSAGE     = 'message';

    const TYPE_REGION_NAME = 'region-name';

    const TYPE_STATE       = 'state';

    const TYPE_ZONE_NAME   = 'zone-name';

    public static function getPrefix()
    {
        return 'TYPE_';
    }
}