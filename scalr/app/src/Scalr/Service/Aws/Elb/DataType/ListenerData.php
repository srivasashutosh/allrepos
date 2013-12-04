<?php
namespace Scalr\Service\Aws\Elb\DataType;

use Scalr\Service\Aws\Elb\Handler\LoadBalancerHandler;
use Scalr\Service\Aws\ElbException;
use Scalr\Service\Aws\Elb\AbstractElbDataType;

/**
 * ListenerData
 *
 * The Listener data type.
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    19.09.2012
 */
class ListenerData extends AbstractElbDataType
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
     * Specifies the TCP port on which the instance server is listening. This property
     * cannot be modified for the life of the LoadBalancer.
     *
     * @var int
     */
    public $instancePort;

    /**
     * Specifies the protocol to use for routing traffic to back-end instances - HTTP,
     * HTTPS, TCP, or SSL. This property cannot be modified for the life of the
     * LoadBalancer.
     *
     * NOTE. If the front-end protocol is HTTP or HTTPS, InstanceProtocol has
     * to be at the same protocol layer, i.e., HTTP or HTTPS. Likewise, if
     * the front-end protocol is TCP or SSL, InstanceProtocol has to be TCP
     * or SSL.
     *
     * NOTE. If there is another listener with the same InstancePort whose
     * InstanceProtocol is secure, i.e., HTTPS or SSL, the listener's
     * InstanceProtocol has to be secure, i.e., HTTPS or SSL. If there
     * is another listener with the same InstancePort whose
     * InstanceProtocol is HTTP or TCP, the listener's
     * InstanceProtocol must be either HTTP or TCP.
     *
     * @var string
     */
    public $instanceProtocol;

    /**
     * Specifies the external LoadBalancer port number. This property cannot be
     * modified for the life of the LoadBalancer.
     *
     * @var int
     */
    public $loadBalancerPort;

    /**
     * Specifies the LoadBalancer transport protocol to use for routing - HTTP,
     * HTTPS, TCP or SSL. This property cannot be modified for the life of the
     * LoadBalancer.
     *
     * @var string
     */
    public $protocol;

    /**
     * The ARN string of the server certificate. To get the ARN of the server certificate,
     * call the AWS Identity and Access Management UploadServerCertificate API.
     *
     * @var string
     */
    public $sslCertificateId;

    /**
     * Constructor
     *
     * @param int     $loadBalancerPort optional A load balancer port
     * @param int     $instancePort optional     An instance port
     * @param string  $protocol optional         Protocol
     * @param string  $instanceProtocol optional An instance protocol
     * @param string  $sslCertificateId optional SSL CertificateId
     */
    public function __construct($loadBalancerPort = null, $instancePort = null, $protocol = null,
                                $instanceProtocol = null, $sslCertificateId = null)
    {
        $this->loadBalancerPort = $loadBalancerPort;
        $this->instancePort = $instancePort;
        $this->protocol = $protocol;
        $this->instanceProtocol = $instanceProtocol ?  : $protocol;
        $this->sslCertificateId = $sslCertificateId;
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\Elb.AbstractElbDataType::throwExceptionIfNotInitialized()
     */
    protected function throwExceptionIfNotInitialized()
    {
        parent::throwExceptionIfNotInitialized();
        if ($this->loadBalancerPort == null) {
            throw new ElbException('loadBalancerPort has not set for the ' . get_class($this));
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
        return $this->getElb()->loadBalancer->deleteListeners($this->getLoadBalancerName(), $this->loadBalancerPort);
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
            $sslCertificateId = $this->sslCertificateId;
        }
        return $this->getElb()->loadBalancer->setListenerSslCertificate(
            $this->getLoadBalancerName(), $this->loadBalancerPort, $sslCertificateId
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
            $this->getLoadBalancerName(), $this->loadBalancerPort, $policyNamesList
        );
    }

    public function toString()
    {
        return ($this->loadBalancerPort !== null ? (string)$this->loadBalancerPort : '') . ';'
             . ($this->instancePort !== null ? (string)$this->instancePort : '') . ';'
             . ($this->protocol !== null ? (string)$this->protocol : '') . ';'
             . ($this->sslCertificateId !== null ? (string)$this->sslCertificateId : '') . ''
             . ($this->instanceProtocol !== null ? (string)$this->instanceProtocol : '') . ';';
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }
}