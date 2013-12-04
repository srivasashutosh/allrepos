<?php
namespace Scalr\Service\Aws\Ec2\Handler;

use Scalr\Service\Aws\Ec2\DataType\MonitorInstancesResponseSetList;
use Scalr\Service\Aws\Ec2\DataType\InstanceAttributeType;
use Scalr\Service\Aws\Ec2\DataType\GetConsoleOutputResponseData;
use Scalr\Service\Aws\Ec2\DataType\InstanceStateChangeList;
use Scalr\Service\Aws\Ec2\DataType\ReservationData;
use Scalr\Service\Aws\Ec2\DataType\RunInstancesRequestData;
use Scalr\Service\Aws\Ec2\DataType\ReservationList;
use Scalr\Service\Aws\Ec2\DataType\InstanceStatusList;
use Scalr\Service\Aws\Ec2\DataType\InstanceFilterData;
use Scalr\Service\Aws\Ec2\DataType\InstanceFilterList;
use Scalr\Service\Aws\Ec2\DataType\InstanceStatusFilterList;
use Scalr\Service\Aws\Ec2\DataType\InstanceData;
use Scalr\Service\Aws\DataType\ListDataType;
use Scalr\Service\Aws\Client\ClientException;
use Scalr\Service\Aws\DataType\ErrorData;
use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2Handler;

/**
 * InstanceHandler
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     10.01.2013
 */
class InstanceHandler extends AbstractEc2Handler
{

    /**
     * Gets InstanceData object from the EntityManager.
     * You should be aware of the fact that the entity manager is turned off by default.
     *
     * @param   string                    $instanceId Identifier.
     * @return  \Scalr\Service\Aws\Ec2\DataType\InstanceData|null    Returns InstanceData if it does exist in the cache or NULL otherwise.
     */
    public function get($instanceId)
    {
        return $this->getEc2()->getEntityManager()->getRepository('Ec2:Instance')->find($instanceId);
    }

    /**
     * DescribeInstances action
     *
     * Describes one or more of your instances.
     * If you specify one or more instance IDs, Amazon EC2 returns information for those instances.
     * If you do not specify instance IDs, Amazon EC2 returns information for all relevant instances.
     * If you specify an invalid instance ID, an error is returned.
     * If you specify an instance that you do not own, it is not included in the returned results.
     * Recently terminated instances might appear in the returned results.
     * This interval is usually less than one hour.
     *
     * @param   ListDataType|array|string                    $instanceIdList optional One or more instance IDs
     * @param   InstanceFilterList|InstanceFilterData|array  $filter         optional A Filter list
     * @return  ReservationList                              Returns List of the reservations on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function describe($instanceIdList = null, $filter = null)
    {
        if ($instanceIdList !== null && !($instanceIdList instanceof ListDataType)) {
            $instanceIdList = new ListDataType($instanceIdList);
        }
        if ($filter !== null && !($filter instanceof InstanceFilterList)) {
            $filter = new InstanceFilterList($filter);
        }
        return $this->getEc2()->getApiHandler()->describeInstances($instanceIdList, $filter);
    }

    /**
     * DescribeInstanceStatus action
     *
     * @param   ListDataType|array|string                               $instanceIdList      optional The list of the instance IDs
     * @param   bool                                                    $includeAllInstances optional When true, returns the health status for all instances
     *                                                                                       (for example, running, stopped, pending, shutting down).When
     *                                                                                       false, returns only the health status for running instances.
     * @param   InstanceStatusFilterList|InstanceStatusFilterData|array $filter              optional A Filter
     * @param   string                                                  $nextToken           The next paginated set of results to return
     * @param   int                                                     $maxResults          The maximum number of paginated instance items per response.
     * @return  InstanceStatusList        Returns the list of the InstanceStatusData objects
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function describeStatus($instanceIdList = null, $includeAllInstances = null, $filter = null,
                                   $nextToken = null, $maxResults = null)
    {
        if ($instanceIdList !== null && !($instanceIdList instanceof ListDataType)) {
            $instanceIdList = new ListDataType($instanceIdList);
        }
        if ($filter !== null && !($filter instanceof InstanceStatusFilterList)) {
            $filter = new InstanceStatusFilterList($filter);
        }
        return $this->getEc2()->getApiHandler()->describeInstanceStatus(
            $instanceIdList, $includeAllInstances, $filter, $nextToken, $maxResults
        );
    }

    /**
     * RunInstances action
     *
     * Launches the specified number of instances of an AMI for which you have permissions.
     * If Amazon EC2 cannot launch the minimum number of instances you request, no instances will be
     * launched. If there is insufficient capacity to launch the maximum number of instances you request, Amazon
     * EC2 launches the minimum number specified and allocates the remaining available instances using round robin.
     *
     * Note! Every instance is launched in a security group (created using the CreateSecurityGroup
     * operation). If you don't specify a security group in the RunInstances request, the "default"
     * security group is used.
     *
     * @param   RunInstancesRequestData $request Request data
     * @return  ReservationData         Returns the ReservationData object
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function run(RunInstancesRequestData $request)
    {
        return $this->getEc2()->getApiHandler()->runInstances($request);
    }

    /**
     * TerminateInstances
     *
     * Shuts down one or more instances. This operation is idempotent; if you terminate an instance more than
     * once, each call will succeed.
     * Terminated instances will remain visible after termination (approximately one hour).
     *
     * Note! By default, Amazon EC2 deletes all Amazon EBS volumes that were attached when the instance
     * launched. Amazon EBS volumes attached after instance launch continue running.
     * You can stop, start, and terminate EBS-backed instances.You can only terminate S3-backed instances.
     * What happens to an instance differs if you stop it or terminate it. For example, when you stop an instance,
     * the root device and any other devices attached to the instance persist. When you terminate an instance,
     * the root device and any other devices attached during the instance launch are automatically deleted.
     *
     * @param   ListDataType|array|string $instanceIdList
     * @return  InstanceStateChangeList Returns the InstanceStateChangeList
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function terminate($instanceIdList)
    {
        if ($instanceIdList !== null && !($instanceIdList instanceof ListDataType)) {
            $instanceIdList = new ListDataType($instanceIdList);
        }
        return $this->getEc2()->getApiHandler()->terminateInstances($instanceIdList);
    }

    /**
     * RebootInstances action
     *
     * Requests a reboot of one or more instances. This operation is asynchronous; it only queues a request
     * to reboot the specified instance(s). The operation will succeed if the instances are valid and belong to
     * you. Requests to reboot terminated instances are ignored.
     *
     * Note! If a Linux/UNIX instance does not cleanly shut down within four minutes, Amazon EC2 will
     * perform a hard reboot.
     *
     * @param   ListDataType|array|string $instanceIdList The list of the Instance IDs
     * @return  bool         Returns true on success or throws an exception otherwise
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function reboot($instanceIdList)
    {
        if ($instanceIdList !== null && !($instanceIdList instanceof ListDataType)) {
            $instanceIdList = new ListDataType($instanceIdList);
        }
        return $this->getEc2()->getApiHandler()->rebootInstances($instanceIdList);
    }

    /**
     * GetConsoleOutput action
     *
     * Retrieves console output for the specified instance.
     * Instance console output is buffered and posted shortly after instance boot, reboot, and termination.
     * Amazon EC2 preserves the most recent 64 KB output which will be available for at least one hour after
     * the most recent post.
     *
     * @param   string      $instanceId       The ID of the EC2 instance.
     * @return  GetConsoleOutputResponseData  Returns object which represents console output.
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function getConsoleOutput($instanceId)
    {
        return $this->getEc2()->getApiHandler()->getConsoleOutput($instanceId);
    }

    /**
     * ModifyInstanceAttribute action
     *
     * Modifies the specified attribute of the specified instance.
     * You can specify only one attribute at a time.
     * To modify some attributes, the instance must be stopped.
     *
     * @param   string                       $instanceId The ID of the Instance.
     * @param   InstanceAttributeType|string $attribute  The attribute name.
     * @param   mixed                        $value      The attribute value can be string, boolean,
     *                                                   array or object depends on attribute name.
     * @return  bool                         Returns TRUE on success
     * @throws  ClientException
     * @throws  Ec2Exception
     * @throws  \BadFunctionCallException
     */
    public function modifyAttribute($instanceId, $attribute, $value)
    {
        if (!($attribute instanceof InstanceAttributeType)) {
            $attribute = new InstanceAttributeType($attribute);
        }
        return $this->getEc2()->getApiHandler()->modifyInstanceAttribute($instanceId, $attribute, $value);
    }

    /**
     * StopInstances action
     *
     * Stops an Amazon EBS-backed instance. Each time you transition an instance from stopped to started,
     * we charge a full instance hour, even if transitions happen multiple times within a single hour.
     *
     * Important!
     * Although Spot Instances can use Amazon EBS-backed AMIs, they don't support Stop/Start. In
     * other words, you can't stop and start Spot Instances launched from an AMI with an Amazon EBS
     * root device.
     *
     * Instances that use Amazon EBS volumes as their root devices can be quickly stopped and started.When
     * an instance is stopped, the compute resources are released and you are not billed for hourly instance
     * usage. However, your root partition Amazon EBS volume remains, continues to persist your data, and
     * you are charged for Amazon EBS volume usage.You can restart your instance at any time.
     *
     * Note!
     * Before stopping an instance, make sure it is in a state from which it can be restarted. Stopping
     * an instance does not preserve data stored in RAM.
     * Performing this operation on an instance that uses an instance store as its root device returns
     * an error.
     *
     * You can stop, start, and terminate EBS-backed instances.You can only terminate S3-backed instances.
     * What happens to an instance differs if you stop it or terminate it. For example, when you stop an instance,
     * the root device and any other devices attached to the instance persist. When you terminate an instance,
     * the root device and any other devices attached during the instance launch are automatically deleted
     *
     * @param   ListDataType|array|string $instanceIdList One or more instance IDs.
     *
     * @param   bool         $force          optional
     *          Forces the instance to stop. The instance will not have an
     *          opportunity to flush file system caches or file system
     *          metadata. If you use this option, you must perform file
     *          system check and repair procedures. This option is not
     *          recommended for Windows instances.
     *
     * @return  InstanceStateChangeList  Return the InstanceStateChangeList
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function stop($instanceIdList, $force = null)
    {
        if (!($instanceIdList instanceof ListDataType)) {
            $instanceIdList = new ListDataType($instanceIdList);
        }
        return $this->getEc2()->getApiHandler()->stopInstances($instanceIdList, $force);
    }

    /**
     * StartInstances action
     *
     * Starts an Amazon EBS-backed AMI that you've previously stopped.
     *
     * Instances that use Amazon EBS volumes as their root devices can be quickly stopped and started.
     * When an instance is stopped, the compute resources are released and you are not billed for hourly instance
     * usage. However, your root partition Amazon EBS volume remains, continues to persist your data, and
     * you are charged for Amazon EBS volume usage. You can restart your instance at any time. Each time
     * you transition an instance from stopped to started, we charge a full instance hour, even if transitions
     * happen multiple times within a single hour.
     *
     * Note! Before stopping an instance, make sure it is in a state from which it can be restarted.
     * Stopping an instance does not preserve data stored in RAM.
     * Performing this operation on an instance that uses an instance store as its root device returns
     * an error.
     *
     * @param   ListDataType|array|string $instanceIdList One or more instance IDs.
     *
     * @return  InstanceStateChangeList  Return the InstanceStateChangeList
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function start($instanceIdList)
    {
        if (!($instanceIdList instanceof ListDataType)) {
            $instanceIdList = new ListDataType($instanceIdList);
        }
        return $this->getEc2()->getApiHandler()->startInstances($instanceIdList);
    }

    /**
     * MonitorInstances action
     *
     * Enables monitoring for a running instance.
     *
     * @param   ListDataType|array|string $instanceIdList One or more instance IDs
     * @return  MonitorInstancesResponseSetList  Returns the MonitorInstancesResponseSetList
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function monitor($instanceIdList)
    {
       if (!($instanceIdList instanceof ListDataType)) {
            $instanceIdList = new ListDataType($instanceIdList);
        }
        return $this->getEc2()->getApiHandler()->monitorInstances($instanceIdList);
    }

    /**
     * UnmonitorInstances action
     *
     * Disables monitoring for a running instance.
     *
     * @param   ListDataType|array|string $instanceIdList One or more instance IDs
     * @return  MonitorInstancesResponseSetList  Returns the MonitorInstancesResponseSetList
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function unmonitor($instanceIdList)
    {
       if (!($instanceIdList instanceof ListDataType)) {
            $instanceIdList = new ListDataType($instanceIdList);
        }
        return $this->getEc2()->getApiHandler()->unmonitorInstances($instanceIdList);
    }
}