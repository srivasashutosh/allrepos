<?php
namespace Scalr\Service\Aws\Repository;

use Scalr\Service\Aws\Ec2\DataType\AvailabilityZoneData;
use Scalr\Service\Aws\AbstractRepository;

/**
 * Ec2AvailabilityZoneRepository
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     27.12.2012
 */
class Ec2AvailabilityZoneRepository extends AbstractRepository
{

    /**
     * Reflection class name.
     * @var string
     */
    private static $reflectionClassName = 'Scalr\\Service\\Aws\\Ec2\\DataType\\AvailabilityZoneData';

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
        return 'zoneName';
    }

    /**
     * Finds one element in entity manager by id
     *
     * @param    string               $id    A Zone Name
     * @return   AvailabilityZoneData Returns AvailabilityZoneData or NULL if nothing found.
     */
    public function find($id)
    {
        return parent::find($id);
    }
}