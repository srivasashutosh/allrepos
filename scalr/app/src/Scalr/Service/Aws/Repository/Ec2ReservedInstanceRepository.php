<?php
namespace Scalr\Service\Aws\Repository;

use Scalr\Service\Aws\Ec2\DataType\ReservedInstanceData;
use Scalr\Service\Aws\AbstractRepository;

/**
 * Ec2ReservedInstanceRepository
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     15.01.2013
 */
class Ec2ReservedInstanceRepository extends AbstractRepository
{

    /**
     * Reflection class name.
     * @var string
     */
    private static $reflectionClassName = 'Scalr\\Service\\Aws\\Ec2\\DataType\\ReservedInstanceData';

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
        return 'reservedInstancesId';
    }

    /**
     * Finds one element in entity manager by $instanceId
     *
     * @param    string                 $id     ReservedInstanceId
     * @return   ReservedInstanceData   Returns ReservedInstanceData or NULL if nothing found.
     */
    public function find($id)
    {
        return parent::find($id);
    }
}