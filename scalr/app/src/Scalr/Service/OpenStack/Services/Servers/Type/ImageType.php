<?php
namespace Scalr\Service\OpenStack\Services\Servers\Type;

use Scalr\Service\OpenStack\Type\StringType;

/**
 * ImageType
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    12.12.2012
 */
class ImageType extends StringType
{

    /**
     * Base OpenStack image
     */
    const TYPE_BASE = 'BASE';

    /**
     * Custom image
     */
    const TYPE_SERVER = 'SERVER';

    public static function getPrefix()
    {
        return 'TYPE_';
    }
}