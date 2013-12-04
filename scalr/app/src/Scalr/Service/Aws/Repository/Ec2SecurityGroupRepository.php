<?php
namespace Scalr\Service\Aws\Repository;

use Scalr\Service\Aws\Ec2\DataType\SecurityGroupData;
use Scalr\Service\Aws\AbstractRepository;

/**
 * Ec2SecurityGroupRepository
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     28.12.2012
 */
class Ec2SecurityGroupRepository extends AbstractRepository
{

    /**
     * Reflection class name.
     * @var string
     */
    private static $reflectionClassName = 'Scalr\\Service\\Aws\\Ec2\\DataType\\SecurityGroupData';

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
        return 'groupId';
    }

    /**
     * Finds one element in entity manager by id
     *
     * @param    string               $id     An groupId
     * @return   SecurityGroupData    Returns SecurityGroupData or NULL if nothing found.
     */
    public function find($id)
    {
        return parent::find($id);
    }
}