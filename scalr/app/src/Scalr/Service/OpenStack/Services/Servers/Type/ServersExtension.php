<?php

namespace Scalr\Service\OpenStack\Services\Servers\Type;

use Scalr\Service\OpenStack\Type\StringType;

/**
 * ServersExtension
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    12.12.2012
 */
class ServersExtension extends StringType
{
    const EXT_MULTINIC    = 'Multinic';
    const EXT_DISK_CONFIG = 'DiskConfig';
    const EXT_EXTENDED_SERVER_ATTRIBUTES = 'ExtendedServerAttributes';
    const EXT_EXTENDED_STATUS = 'ExtendedStatus';
    const EXT_FLAVOR_DISABLED = 'FlavorDisabled';
    const EXT_FLAVOR_EXTRA_DATA = 'FlavorExtraData';
    const EXT_SCHEDULER_HINTS = 'SchedulerHints';
    const EXT_ADMIN_ACTIONS   = 'AdminActions';
    const EXT_AGGREGATES = 'Aggregates';
    const EXT_AVAILABILITY_ZONE = 'AvailabilityZone';
    const EXT_CERTIFICATES = 'Certificates';
    const EXT_CLOUDPIPE = 'Cloudpipe';
    const EXT_CONFIG_DRIVE = 'ConfigDrive';
    const EXT_CONSOLE_OUTPUT = 'ConsoleOutput';
    const EXT_CONSOLES = 'Consoles';
    const EXT_CREATESERVEREXT = 'Createserverext';
    const EXT_DEFERRED_DELETE = 'DeferredDelete';
    const EXT_FLAVOR_ACCESS = 'FlavorAccess';
    const EXT_FLAVOR_EXTRA_SPECS = 'FlavorExtraSpecs';
    const EXT_FLAVOR_MANAGE = 'FlavorManage';
    const EXT_FLAVOR_RXTX = 'FlavorRxtx';
    const EXT_FLAVOR_SWAP = 'FlavorSwap';
    const EXT_FLOATING_IP_DNS = 'FloatingIpDns';
    const EXT_FLOATING_IP_POOLS = 'FloatingIpPools';
    const EXT_FLOATING_IPS = 'FloatingIps';
    const EXT_HOSTS = 'Hosts';
    const EXT_HYPERVISORS = 'Hypervisors';
    const EXT_OS_INSTANCE_USAGE_AUDIT_LOG = 'OSInstanceUsageAuditLog';
    const EXT_KEYPAIRS = 'Keypairs';
    const EXT_MULTIPLE_CREATE = 'MultipleCreate';
    const EXT_NETWORKS = 'Networks';
    const EXT_QUOTA_CLASSES = 'QuotaClasses';
    const EXT_QUOTAS = 'Quotas';
    const EXT_RESCUE = 'Rescue';
    const EXT_SECURITY_GROUPS = 'SecurityGroups';
    const EXT_SERVER_DIAGNOSTICS = 'ServerDiagnostics';
    const EXT_SERVER_START_STOP = 'ServerStartStop';
    const EXT_SIMPLE_TENANT_USAGE = 'SimpleTenantUsage';
    const EXT_USED_LIMITS = 'UsedLimits';
    const EXT_USER_DATA = 'UserData';
    const EXT_VIRTUAL_INTERFACES = 'VirtualInterfaces';
    const EXT_VOLUME_TYPES = 'VolumeTypes';
    const EXT_VOLUMES = 'Volumes';

    public static function getPrefix()
    {
        return 'EXT_';
    }
}