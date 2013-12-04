<?php
namespace Scalr\Service\Aws\DataType;

/**
 * Error Data
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     03.10.2012
 */
class ErrorData
{

    /**
     * The request signature does not conform to AWS standards.
     */
    const ERR_INCOMPLETE_SIGNATURE = 'IncompleteSignature';

    /**
     * The request processing has failed due to some unknown error, exception or failure.
     */
    const ERR_INTERNAL_FAILURE = 'InternalFailure';

    /**
     * The action or operation requested is invalid.
     */
    const ERR_INVALID_ACTION = 'InvalidAction';

    /**
     * The X.509 certificate or AWS Access Key ID provided does not exist in our records.
     */
    const ERR_INVALID_CLIENT_TOKEN_ID = 'InvalidClientTokenId';

    /**
     * Parameters that must not be used together were used together.
     */
    const ERR_INVALID_PARAMETER_COMBINATION = 'InvalidParameterCombination';

    /**
     * A bad or out-of-range value was supplied for the input parameter.
     */
    const ERR_INVALID_PARAMETER_VALUE = 'InvalidParameterValue';

    /**
     * AWS query string is malformed, does not adhere to AWS standards.
     */
    const ERR_INVALID_QUERY_PARAMETER = 'InvalidQueryParameter';

    /**
     * The query string is malformed.
     */
    const ERR_MALFORMED_QUERY_STRING = 'MalformedQueryString';

    /**
     * The request is missing an action or operation parameter.
     */
    const ERR_MISSING_ACTION = 'MissingAction';

    /**
     * Request must contain either a valid (registered) AWS Access Key ID or X.509 certificate.
     */
    const ERR_MISSING_AUTHENTICATION_TOKEN = 'MissingAuthenticationToken';

    /**
     * An input parameter that is mandatory for processing the request is not supplied.
     */
    const ERR_MISSING_PARAMETER = 'MissingParameter';

    /**
     * Request is past expires date or the request
     * date (either with 15 minute padding), or the
     * request date occurs more than 15 minutes in
     * the future.
     */
    const ERR_REQUEST_EXPIRED = 'RequestExpired';

    /**
     * The request has failed due to a temporary failure of the server.
     */
    const ERR_SERVICE_UNAVAILABLE = 'ServiceUnavailable';

    /**
     * Request was denied due to request throttling.
     */
    const ERR_THROTTLING = 'Throttling';

    /**
     * The AWS Access Key ID needs a subscription for the service.
     */
    const ERR_OPT_IN_REQUIRED = 'OptInRequired';

    /**
     * The specified LoadBalancer could not be found.
     */
    const ERR_LOAD_BALANCER_NOT_FOUND = 'LoadBalancerNotFound';

    /**
     * The specified SSL ID does not refer to a valid SSL
     * certificate in the AWS Identity and Access Management Service.
     */
    const ERR_CERTIFICATE_NOT_FOUND = 'CertificateNotFound';

    /**
     * LoadBalancer name already exists for this account.
     * Please choose another name.
     */
    const ERR_DUPLICATE_LOAD_BALANCER_NAME = 'DuplicateLoadBalancerName';

    /**
     * Requested configuration change is invalid.
     */
    const ERR_INVALID_CONFIGURATION_REQUEST = 'InvalidConfigurationRequest';

    /**
     * Invalid value for scheme. Scheme can only be specified for load balancers in VPC.
     */
    const ERR_INVALID_SCHEME = 'InvalidScheme';

    /**
     * One or more specified security groups do not exist.
     */
    const ERR_INVALID_SECURITY_GROUP = 'InvalidSecurityGroup';

    /**
     * The VPC has no Internet gateway.
     */
    const ERR_INVALID_SUBNET = 'InvalidSubnet';

    /**
     * One or more subnets were not found.
     */
    const ERR_SUBNET_NOT_FOUND = 'SubnetNotFound';

    /**
     * A Listener already exists for the given LoadBalancerName and LoadBalancerPort, but
     * with a different InstancePort, Protocol, or SSLCertificateId.
     */
    const ERR_DUPLICATE_LISTENER = 'DuplicateListener';

    /**
     * The specified EndPoint is not valid.
     */
    const ERR_INVALID_INSTANCE = 'InvalidInstance';

    /**
     * LoadBalancer does not have a listener configured at
     * the given port.
     */
    const ERR_LISTENER_NOT_FOUND = 'ListenerNotFound';

    /**
     * Policy with the same name exists for this LoadBalancer.
     * Please choose another name.
     */
    const ERR_DUPLICATE_POLICY_NAME = 'DuplicatePolicyName';

    /**
     * Quota for number of policies for this LoadBalancer
     * has already been reached.
     */
    const ERR_TOO_MANY_POLICIES = 'TooManyPolicies';

    /**
     * One or more specified policies were not found.
     */
    const ERR_POLICY_NOT_FOUND = 'PolicyNotFound';

    /**
     * Unexpected complex element termination
     */
    const ERR_MALFORMED_INPUT = 'MalformedInput';

    /**
     * Indicates that the request processing has failed due to some
     * unknown error, exception, or failure.
     */
    const ERR_INTERNAL_SERVICE = 'InternalService';

    /**
     * You must wait 60 seconds after deleting a queue before you can create another with the same name.
     */
    const ERR_SQS_QUEUE_DELETED_RECENTLY = 'AWS.SimpleQueueService.QueueDeletedRecently';

    /**
     * Queue already exists. SQS returns this error only if the request includes an attribute
     * name or value that differs from the name or value for the existing attribute.
     */
    const ERR_SQS_QUEUE_NAME_EXISTS = 'AWS.SimpleQueueService.QueueNameExists';

    /**
     * The message contains characters outside the allowed set.
     */
    const ERR_SQS_INVALID_MESSAGE_CONTENTS = 'InvalidMessageContents';

    /**
     * The message contains characters outside the allowed set.
     */
    const ERR_SQS_MESSAGE_TOO_LONG = 'MessageTooLong';

    /**
     * The request was rejected because it attempted to create a
     * resource that already exists.
     */
    const ERR_ENTITY_ALREADY_EXISTS = 'EntityAlreadyExists';

    /**
     * The request was rejected because it attempted to create resources
     * beyond the current AWS account limits.
     */
    const ERR_LIMIT_EXCEEDED = 'LimitExceeded';

    /**
     * The request was rejected because it referenced an entity that does not exist.
     * The error message describes the entity.
     */
    const ERR_NO_SUCH_ENTITY = 'NoSuchEntity';

    /**
     * DBInstanceIdentifier does not refer to an existing DB Instance.
     */
    const ERR_DB_INSTANCE_NOT_FOUND = 'DBInstanceNotFound';

    /**
     * DBSnapshotIdentifier is already used by an existing snapshot
     */
    const ERR_DB_SNAPSHOT_ALREADY_EXISTS = 'DBSnapshotAlreadyExists';

    /**
     * The specified DB Instance is not in the available state.
     */
    const ERR_INVALID_DB_INSTANCE_STATE = 'InvalidDBInstanceState';

    /**
     * Request would result in user exceeding the allowed number of DB Snapshots.
     */
    const ERR_SNAPSHOT_QUOTA_EXCEEDED = 'SnapshotQuotaExceeded';

    /**
     * The specified CIDRIP or EC2 security group is already authorized for the specified DB security group.
     */
    const ERR_AUTHORIZATION_ALREADY_EXISTS = 'AuthorizationAlreadyExists';

    /**
     * Database security group authorization quota has been reached.
     */
    const ERR_AUTHORIZATION_QUOTA_EXCEEDED = 'AuthorizationQuotaExceeded';

    /**
     * DBSecurityGroupName does not refer to an existing DB Security Group.
     */
    const ERR_DB_SECURITY_GROUP_NOT_FOUND = 'DBSecurityGroupNotFound';

    /**
     * The state of the DB Security Group does not allow deletion
     */
    const ERR_INVALID_DB_SECURITY_GROUP_STATE = 'InvalidDBSecurityGroupState';

    /**
     * The state of the DB Security Snapshot does not allow deletion
     */
    const ERR_INVALID_DB_SNAPSHOT_STATE = 'InvalidDBSnapshotState';

    /**
     * DBSnapshotIdentifier does not refer to an existing DB Snapshot.
     */
    const ERR_DB_SNAPSHOT_NOT_FOUND = 'DBSnapshotNotFound';

    /**
     * Error type
     * @var string
     */
    protected $type;

    /**
     * Error Code
     * @var string
     */
    protected $code;

    /**
     * Error Message
     * @var string
     */
    protected $message;

    /**
     * RequestId
     *
     * @var string
     */
    protected $requestId;

    /**
     * Raw request message
     *
     * @var \HttpRequest
     */
    public $request;

    /**
     * Magic setter
     *
     * @param    string    $property
     * @param    mixed     $value
     */
    public function __set($property, $value)
    {
        if (property_exists($this, $property)) {
            $this->{$property} = $value;
        }
    }

    /**
     * Gets Error Type
     *
     * @return string Returns Error Type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Gets Error Code
     *
     * @return string Returns Error Code
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Gets Error Message
     *
     * @return string Returns Error Message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Gets RequestId
     *
     * @return string Returns RequestId
     */
    public function getRequestId()
    {
        return $this->requestId;
    }
}