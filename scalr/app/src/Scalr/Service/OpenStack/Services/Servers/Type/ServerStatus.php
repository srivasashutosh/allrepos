<?php
namespace Scalr\Service\OpenStack\Services\Servers\Type;

use Scalr\Service\OpenStack\Type\StringType;

/**
 * ServerStatus
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    10.12.2012
 */
class ServerStatus extends StringType
{
    /**
     * The server is active and ready to use.
     */
    const STATUS_ACTIVE = 'ACTIVE';

    /**
     * The server is being built.
     */
    const STATUS_BUILD = 'BUILD';

    /**
     * The server was deleted. The list servers API operation does not show servers
     * with a status of DELETED. To list deleted servers, use the changes-since parameter.
     */
    const STATUS_DELETED = 'DELETED';

    /**
     * The requested operation failed and the server is in an error state
     */
    const STATUS_ERROR = 'ERROR';

    /**
     * The server is going through a hard reboot. A hard reboot power cycles
     * your server, which performs an immediate shutdown and restart.
     */
    const STATUS_HARD_REBOOT = 'HARD_REBOOT';

    /**
     * The server is being moved from one physical node to another physical node
     */
    const STATUS_MIGRATING = 'MIGRATING';

    /**
     * The password for the server is being changed.
     */
    const STATUS_PASSWORD = 'PASSWORD';

    /**
     * The server is going through a soft reboot. During a soft reboot, the operating
     * system is signaled to restart, which allows for a graceful shutdown and restart of all
     * processes.
     */
    const STATUS_REBOOT = 'REBOOT';

    /**
     * The server is being rebuilt from an image.
     */
    const STATUS_REBUILD = 'REBUILD';

    /**
     * The server is in rescue mode.
     */
    const STATUS_RESCUE = 'RESCUE';

    /**
     * The server is being resized and is inactive until the resize operation completes
     */
    const STATUS_RESIZE = 'RESIZE';

    /**
     * A resized or migrated server is being reverted to its previous size.
     */
    const STATUS_REVERT_RESIZE = 'REVERT_RESIZE';

    /**
     * The server is inactive, either by request or necessity.
     */
    const STATUS_SUSPENDED = 'SUSPENDED';

    /**
     * The server is in an unknown state. Contact OpenStack support.
     */
    const STATUS_UNKNOWN = 'UNKNOWN';

    /**
     * The server is waiting for the resize operation to be confirmed so that
     * the original server can be removed.
     */
    const STATUS_VERIFY_RESIZE = 'VERIFY_RESIZE';

    public static function getPrefix()
    {
        return 'STATUS_';
    }
}