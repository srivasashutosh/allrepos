<?php
namespace Scalr\Service\Aws\Repository;

use Scalr\Service\Aws\Ec2\DataType\NetworkInterfaceData;
use Scalr\Service\Aws\AbstractRepository;

/**
 * Ec2NetworkInterfaceRepository
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     02.04.2013
 */
class Ec2NetworkInterfaceRepository extends AbstractRepository
{

    /**
     * Reflection class name.
     * @var string
     */
    private static $reflectionClassName = 'Scalr\\Service\\Aws\\Ec2\\DataType\\NetworkInterfaceData';

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
        return 'networkInterfaceId';
    }

    /**
     * Finds one element in entity manager by its unique Identifier
     *
     * @param    string               $id    NetworkInterfaceId
     * @return   NetworkInterfaceData Returns NetworkInterfaceData or NULL if nothing found.
     */
    public function find($id)
    {
        return parent::find($id);
    }
}