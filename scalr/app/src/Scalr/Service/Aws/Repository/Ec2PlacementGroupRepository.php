<?php
namespace Scalr\Service\Aws\Repository;

use Scalr\Service\Aws\Ec2\DataType\PlacementGroupData;
use Scalr\Service\Aws\AbstractRepository;

/**
 * Ec2PlacementGroupRepository
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     31.01.2013
 */
class Ec2PlacementGroupRepository extends AbstractRepository
{

    /**
     * Reflection class name.
     * @var string
     */
    private static $reflectionClassName = 'Scalr\\Service\\Aws\\Ec2\\DataType\\PlacementGroupData';

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
        return 'groupName';
    }

    /**
     * Finds one element in entity manager by unique identifier
     *
     * @param    string             $id     GroupName
     * @return   PlacementGroupData Returns PlacementGroupData or NULL if nothing found.
     */
    public function find($id)
    {
        return parent::find($id);
    }
}