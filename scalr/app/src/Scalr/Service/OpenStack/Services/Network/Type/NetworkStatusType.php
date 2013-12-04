<?php
namespace Scalr\Service\OpenStack\Services\Network\Type;

use Scalr\Service\OpenStack\Type\StringType;

/**
 * NetworkStatusType
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    08.05.2013
 */
class NetworkStatusType extends StringType
{
    const STATUS_ACTIVE = 'ACTIVE';

    public static function getPrefix()
    {
        return 'STATUS_';
    }
}