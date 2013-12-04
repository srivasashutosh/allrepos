<?php
namespace Scalr\Service\OpenStack\Services\Servers\Type;

use Scalr\Service\OpenStack\Type\StringType;

/**
 * ImageStatus
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    12.12.2012
 */
class ImageStatus extends StringType
{

    /**
     * Images with this status are available for use.
     */
    const STATUS_ACTIVE = 'ACTIVE';

    const STATUS_DELETED = 'DELETED';

    const STATUS_ERROR = 'ERROR';

    const STATUS_SAVING = 'SAVING';

    const STATUS_UNKNOWN = 'UNKNOWN';


    public static function getPrefix()
    {
        return 'STATUS_';
    }
}