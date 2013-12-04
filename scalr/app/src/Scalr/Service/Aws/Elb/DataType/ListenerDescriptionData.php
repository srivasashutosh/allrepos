<?php
namespace Scalr\Service\Aws\Elb\DataType;

use Scalr\Service\Aws\Elb;
use Scalr\Service\Aws\ElbException;
use Scalr\Service\Aws\Elb\AbstractElbDataType;
use Scalr\Service\Aws\Elb\DataType\ListenerData;
use Scalr\Service\Aws\Elb\DataType\ListenerDescriptionData;

/**
 * ListenerDescriptionData
 *
 * The ListenerDescription data type.
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    19.09.2012
 * @property ListenerData     $listener  Specifies the TCP port on which the instance server is listening.
 *                                       This property cannot be modified for the life of the LoadBalancer.
 * @method   ListenerData             getListener()     getListener()
 * @method   ListenerDescriptionData  setListener()     setListener(ListenerData $listener)
 */
class ListenerDescriptionData extends AbstractElbDataType
{

    /**
     * External Identifier names.
     *
     * @var array
     */
    protected $_externalKeys = array(
        'loadBalancerName'
    );

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var array
     */
    protected $_properties = array(
        'listener'
    );

    /**
     * A list of policies enabled for this listener. An empty list indicates that no policies are
     * enabled.
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
        if (!($this->getListener() instanceof ListenerData) ||
            $this->getListener()->loadBalancerPort == null) {
            throw new ElbException('Listener object has not been initialized yet or loadBalancerPort is not set.');
        }
    }

    /**
     * DeleteLoadBalancerListeners action
     *
     * Deletes listener from the LoadBalancer for the specified port.
     *
     * @return  boolean                Returns TRUE if success
     * @throws  ClientException
     */
    public function delete()
    {
        parent::throwExceptionIfNotInitialized();
        return $this->getElb()->loadBalancer->deleteListeners(
            $this->getLoadBalancerName(), $this->getListener()->loadBalancerPort
        );
    }

    /**
     * SetLoadBalancerListenerSSLCertificate action
     *
     * Sets the certificate that terminates the specified listener's SSL connections.
     * The specified certificate replaces any prior certificate that was used on the
     * same LoadBalancer and port.
     *
     * @param   string    $sslCertificateId optional The ID of the SSL certificate chain to use.
     *                                               If sslCertificateId isn't provided as argument it will
     *                                               try to use internal value, that is set for the Listener.
     * @return  boolean   Returns boolean TRUE if success or throws exception
     * @throws  ClientException
     * @throws  ElbException
     */
    public function updateSslCertificate($sslCertificateId = null)
    {
        $this->throwExceptionIfNotInitialized();
        if ($sslCertificateId === null) {
            $sslCertificateId = $this->getListener()->sslCertificateId;
        }
        return $this->getElb()->loadBalancer->setListenerSslCertificate(
            $this->getLoadBalancerName(), $this->getListener(), $sslCertificateId
        );
    }

    /**
     * SetLoadBalancerPoliciesOfListener action
     *
     * Associates, updates, or disables a policy with a listener on the LoadBalancer.
     * You can associate multiple policies with a listener.
     *
     * @param   string|array|ListDataType $PolicyNamesList  optional List of policies to be associated with the listener.
     *                                                      Currently this list can have at most one policy.
     *                                                      If the list is empty, the current policy is removed from the listener.
     * @return  array        Returns updated policies which are associated with the listener if success,
     *                       or throws an exception if failure.
     * @throws  ClientException
     * @throws  ElbException
     */
    public function setPolicies($policyNamesList = null)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getElb()->loadBalancer->setPoliciesOfListener(
            $this->getLoadBalancerName(), $this->getListener(), $policyNamesList
        );
    }
}