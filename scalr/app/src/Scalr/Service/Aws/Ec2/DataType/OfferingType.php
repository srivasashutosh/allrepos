<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\DataType\StringType;

/**
 * OfferingType
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    14.01.2013
 */
class OfferingType extends StringType
{
    const TYPE_HEAVY_UTILIZATION  = 'Heavy Utilization';

    const TYPE_MEDIUM_UTILIZATION = 'Medium Utilization';

    const TYPE_LIGHT_UTILIZATION  = 'Light Utilization';

    protected static function getPrefix()
    {
        return 'TYPE_';
    }
}