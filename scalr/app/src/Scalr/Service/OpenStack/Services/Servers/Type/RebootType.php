<?php
namespace Scalr\Service\OpenStack\Services\Servers\Type;

use Scalr\Service\OpenStack\Type\StringType;

/**
 * RebootType
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    18.12.2012
 */
class RebootType extends StringType
{

    /**
     * The operating system is signaled to restart, which allows for a
     * graceful shutdown and restart of all processes.
     */
    const TYPE_SOFT = 'SOFT';

    /**
     * Power cycles your server, which performs an immediate
     * shutdown and restart.
     */
    const TYPE_HARD = 'HARD';

    public static function getPrefix()
    {
        return 'TYPE_';
    }
}