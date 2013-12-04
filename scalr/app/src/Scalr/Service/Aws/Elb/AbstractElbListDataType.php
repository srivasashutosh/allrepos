<?php
namespace Scalr\Service\Aws\Elb;

use Scalr\Service\Aws;
use Scalr\Service\Aws\DataType\ListDataType;

/**
 * AbstractElbListDataType
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     13.10.2012
 * @property  \Scalr\Service\Aws\Elb   $elb            An Amazon ELB instance
 * @method    \Scalr\Service\Aws\Elb   getElb()        getElb()                                   Gets an Amazon ELB instance.
 * @method    AbstractElbListDataType  setElb()        setElb(\Scalr\Service\Aws\Elb $elb)        Sets an Amazon Elb instance.
 */
abstract class AbstractElbListDataType extends ListDataType
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
