<?php
namespace Scalr\Service\Aws\Repository;

use Scalr\Service\Aws\Ec2\DataType\InstanceData;
use Scalr\Service\Aws\AbstractRepository;

/**
 * Ec2InstanceRepository
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     10.01.2013
 */
class Ec2InstanceRepository extends AbstractRepository
{

    /**
     * Reflection class name.
     * @var string
     */
    private static $reflectionClassName = 'Scalr\\Service\\Aws\\Ec2\\DataType\\InstanceData';

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
        return 'instanceId';
    }

    /**
     * Finds one element in entity manager by $instanceId
     *
     * @param    string               $id  An InstanceId
     * @return   InstanceData         Returns InstanceData or NULL if nothing found.
     */
    public function find($id)
    {
        return parent::find($id);
    }
}