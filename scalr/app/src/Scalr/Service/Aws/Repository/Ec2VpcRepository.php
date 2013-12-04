<?php
namespace Scalr\Service\Aws\Repository;

use Scalr\Service\Aws\Ec2\DataType\VpcData;
use Scalr\Service\Aws\AbstractRepository;

/**
 * Ec2VpcRepository
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     28.03.2013
 */
class Ec2VpcRepository extends AbstractRepository
{

    /**
     * Reflection class name.
     * @var string
     */
    private static $reflectionClassName = 'Scalr\\Service\\Aws\\Ec2\\DataType\\VpcData';

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
        return 'vpcId';
    }

    /**
     * Finds one element in entity manager by its unique Identifier
     *
     * @param    string  $id    VpcId
     * @return   VpcData        Returns VpcData or NULL if nothing found.
     */
    public function find($id)
    {
        return parent::find($id);
    }
}