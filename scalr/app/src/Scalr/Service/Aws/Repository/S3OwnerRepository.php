<?php
namespace Scalr\Service\Aws\Repository;

use Scalr\Service\Aws\S3\DataType\OwnerData;
use Scalr\Service\Aws\AbstractRepository;

/**
 * S3OwnerRepository
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     20.11.2012
 */
class S3OwnerRepository extends AbstractRepository
{

    /**
     * Reflection class name.
     * @var string
     */
    private static $reflectionClassName = 'Scalr\\Service\\Aws\\S3\\DataType\\OwnerData';

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
        return 'ownerid';
    }

    /**
     * Finds one element in entity manager by id
     *
     * @param    string       $id     An owner ID
     * @return   OwnerData    Returns OwnerData or NULL if nothing found.
     */
    public function find($id)
    {
        return parent::find($id);
    }
}