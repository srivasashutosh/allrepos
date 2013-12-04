<?php
namespace Scalr\Service\OpenStack\Services\Volume\Type;

use Scalr\Service\OpenStack\Type\StringType;

/**
 * VolumeStatus
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    18.12.2012
 */
class VolumeStatus extends StringType
{

    const STATUS_CREATING = 'creating';

    const STATUS_AVAILABLE = 'available';

    const STATUS_ATTACHING = 'attaching';

    const STATUS_IN_USE = 'in-use';

    const STATUS_DELETING = 'deleting';

    const STATUS_ERROR = 'error';

    const STATUS_ERROR_DELETING = 'error_deleting';


    public static function getPrefix()
    {
        return 'STATUS_';
    }
}