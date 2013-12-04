<?php
namespace Scalr\Service\Aws\Repository;

use Scalr\Service\Aws\Elb\DataType\LoadBalancerDescriptionData;
use Scalr\Service\Aws\AbstractRepository;

/**
 * ElbLoadBalancerDescriptionRepository
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     03.10.2012
 */
class ElbLoadBalancerDescriptionRepository extends AbstractRepository
{

    /**
     * Reflection class name.
     * @var string
     */
    private static $reflectionClassName = 'Scalr\\Service\\Aws\\Elb\\DataType\\LoadBalancerDescriptionData';

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.AbstractRepository::getReflectionClassName()
     */
    public function getReflectionClassName()
    {
        return self::$reflectionClassName;
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.AbstractRepository::getIdentifier()
     */
    public function getIdentifier()
    {
        return 'loadBalancerName';
    }

    /**
     * Finds one element in entity manager by id
     *
     * @param    string      $id                  LoadBalancerName
     * @return   LoadBalancerDescriptionData|null Returns object or NULL if nothing found.
     */
    public function find($id)
    {
        return parent::find($id);
    }
}