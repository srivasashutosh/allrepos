<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\DataType\StringType;
use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * PlacementGroupFilterNameType
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    31.01.2013
 */
class PlacementGroupFilterNameType extends StringType
{

    /**
     * The name of the placement group
     */
    const TYPE_GROUP_NAME = 'group-name';

    /**
     * The state of the placement group
     */
    const TYPE_STATE = 'state';

    /**
     * The strategy of the placement group
     */
    const TYPE_STRATEGY = 'strategy';

    public static function getPrefix()
    {
        return 'TYPE_';
    }
}