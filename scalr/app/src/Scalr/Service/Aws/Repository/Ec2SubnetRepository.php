<?php
namespace Scalr\Service\Aws\Repository;

use Scalr\Service\Aws\Ec2\DataType\SubnetData;
use Scalr\Service\Aws\AbstractRepository;

/**
 * Ec2SubnetRepository
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     31.01.2013
 */
class Ec2SubnetRepository extends AbstractRepository
{

    /**
     * Reflection class name.
     * @var string
     */
    private static $reflectionClassName = 'Scalr\\Service\\Aws\\Ec2\\DataType\\SubnetData';

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
        return 'subnetId';
    }

    /**
     * Finds one element in entity manager by unique identifier
     *
     * @param    string             $id     SubnetId
     * @return   SubnetData         Returns InstanceData or NULL if nothing found.
     */
    public function find($id)
    {
        return parent::find($id);
    }
}