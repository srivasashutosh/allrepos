<?php
namespace Scalr\Service\Aws\Elb\DataType;

use Scalr\Service\Aws\ElbException;
use Scalr\Service\Aws\Elb\AbstractElbDataType;

/**
 * BackendServerDescriptionData
 *
 * This data type is used as a response element in the DescribeLoadBalancers action
 * to describe the configuration of the back-end server.
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    19.09.2012
 */
class BackendServerDescriptionData extends AbstractElbDataType
{

    /**
     * List of external identifier names.
     *
     * @var array
     */
    protected $_externalKeys = array(
        'loadBalancerName'
    );

    /**
     * Provides the port on which the back-end server is listening.
     *
     * @var int
     */
    public $instancePort;

    /**
     * Provides a list of policy names enabled for the back-end server.
     *
     * @var array
     */
    public $policyNames;

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\Elb.AbstractElbDataType::throwExceptionIfNotInitialized()
     */
    protected function throwExceptionIfNotInitialized()
    {
        parent::throwExceptionIfNotInitialized();
        if ($this->instancePort == null) {
            throw new ElbException('InstancePort property has not been initialized for ' . get_class($this));
        }
    }

    /**
     * SetLoadBalancerPoliciesForBackendServer action
     *
     * Replaces the current set of policies associated with a port on which
     * the back-end server is listening with a new set of policies.
     * After the policies have been created using CreateLoadBalancerPolicy, they
     * can be applied here as a list. At this time, only the back-end server authentication policy type can be
     * applied to the back-end ports; this policy type is composed of multiple public key policies
     *
     * @param   string|array|ListDataType  $policyNamesList  List of policy names to be set. If the list is empty, then all
     *                                                       current polices are removed from the back-end server.
     * @return  array                      Returns updated list of policy names.
     * @throws  ClientException
     * @throws  ElbException
     */
    public function setPolicies($policyNamesList = null)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getElb()->loadBalancer->setPoliciesForBackendServer(
            $this->getLoadBalancerName(), $this->instancePort, $policyNamesList
        );
    }
}