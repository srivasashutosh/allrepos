<?php
namespace Scalr\Service\Aws\Repository;

use Scalr\Service\Aws\Ec2\DataType\ImageData;
use Scalr\Service\Aws\AbstractRepository;

/**
 * Ec2ImageRepository
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     24.01.2013
 */
class Ec2ImageRepository extends AbstractRepository
{

    /**
     * Reflection class name.
     * @var string
     */
    private static $reflectionClassName = 'Scalr\\Service\\Aws\\Ec2\\DataType\\ImageData';

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
        return 'imageId';
    }

    /**
     * Finds one element in entity manager by unique identifier
     *
     * @param    string            $id  An ImageId
     * @return   ImageData         Returns ImageData or NULL if nothing found.
     */
    public function find($id)
    {
        return parent::find($id);
    }
}