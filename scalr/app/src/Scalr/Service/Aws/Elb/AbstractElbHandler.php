<?php
namespace Scalr\Service\Aws\Elb;

use Scalr\Service\Aws;
use Scalr\Service\Aws\AbstractHandler;

/**
 * AbstractElbHandler
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     05.10.2012
 * @property  \Scalr\Service\Aws\Elb   $elb            An Amazon ELB instance
 * @method    \Scalr\Service\Aws\Elb   getElb()        getElb()                                   Gets an Amazon ELB instance.
 * @method    AbstractElbHandler       setElb()        setElb(\Scalr\Service\Aws\Elb $elb)        Sets an Amazon Elb instance.
 * @method    void                     __constructor() __constructor(\Scalr\Service\Aws\Elb $elb)
 */
abstract class AbstractElbHandler extends AbstractHandler
{

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.AbstractDataType::getServiceNames()
     */
    public function getServiceNames()
    {
        return array(Aws::SERVICE_INTERFACE_ELB);
    }
}