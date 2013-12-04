<?php
namespace Scalr\Service\Aws\Repository;

use Scalr\Service\Aws\Iam\DataType\UserData;
use Scalr\Service\Aws\AbstractRepository;

/**
 * IamUserRepository
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     13.11.2012
 */
class IamUserRepository extends AbstractRepository
{

    /**
     * Reflection class name.
     * @var string
     */
    private static $reflectionClassName = 'Scalr\\Service\\Aws\\Iam\\DataType\\UserData';

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
        return 'userName';
    }

    /**
     * Finds one element in entity manager by id
     *
     * @param    string       $id     An User name
     * @return   UserData     Returns UserData or NULL if nothing found.
     */
    public function find($id)
    {
        return parent::find($id);
    }
}