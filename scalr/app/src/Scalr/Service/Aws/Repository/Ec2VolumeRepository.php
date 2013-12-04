<?php
namespace Scalr\Service\Aws\Repository;

use Scalr\Service\Aws\Ec2\DataType\VolumeData;
use Scalr\Service\Aws\AbstractRepository;

/**
 * Ec2VolumeRepository
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     21.01.2013
 */
class Ec2VolumeRepository extends AbstractRepository
{

    /**
     * Reflection class name.
     * @var string
     */
    private static $reflectionClassName = 'Scalr\\Service\\Aws\\Ec2\\DataType\\VolumeData';

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
        return 'volumeId';
    }

    /**
     * Finds one element in entity manager by unique identifier
     *
     * @param    string             $id     VolumeId
     * @return   VolumeData         Returns InstanceData or NULL if nothing found.
     */
    public function find($id)
    {
        return parent::find($id);
    }
}