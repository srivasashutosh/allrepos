<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * StateReasonData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    09.01.2013
 */
class StateReasonData extends AbstractEc2DataType
{
    /**
     * A Spot Instance was terminated due to an increase in the market price.
     */
    const CODE_SERVER_SPOT_INSTANCE_TERMINATION = 'Server.SpotInstanceTermination';

    /**
     * An internal error occurred during instance launch, resulting in termination.
     */
    const CODE_SERVER_INTERNAL_ERROR = 'Server.InternalError';

    /**
     * There was insufficient instance capacity to satisfy the launch request.
     */
    const CODE_SERVER_INSUFFICIENT_INSTANCE_CAPACITY = 'Server.InsufficientInstanceCapacity';

    /**
     * A client error caused the instance to terminate on launch.
     */
    const CODE_CLIENT_INTERNAL_ERROR = 'Client.InternalError';

    /**
     * The instance initiated shutdown by a shutdown -h command issued from inside the instance.
     */
    const CODE_CLIENT_INSTANCE_INITIATED_SHUTDOWN = 'Client.InstanceInitiatedShutdown';

    /**
     * The instance was shutdown by a user via an API call..
     */
    const CODE_CLIENT_USER_INITIATED_SHUTDOWN = 'Client.UserInitiatedShutdown';

    /**
     * The volume limit was exceeded.
     */
    const CODE_CLIENT_VOLUME_LIMIT_EXCEEDED = 'Client.VolumeLimitExceeded';

    /**
     * The specified snapshot was not found.
     */
    const CODE_CLIENT_INVALID_SNAPSHOT_NOT_FOUND = 'Client.InvalidSnapshot.NotFound';

    /**
     * The reason code for the state change.
     * @var string
     */
    public $code;

    /**
     * The message for the state change
     * @var string
     */
    public $message;
}