<?php
namespace Scalr\Service\OpenStack\Services\Volume\Type;

use Scalr\Service\OpenStack\Type\StringType;

/**
 * VolumeType
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    18.12.2012
 */
class VolumeType extends StringType
{

    const TYPE_SATA = 'SATA';

    const TYPE_SSD = 'SSD';

    public static function getPrefix()
    {
        return 'TYPE_';
    }
}