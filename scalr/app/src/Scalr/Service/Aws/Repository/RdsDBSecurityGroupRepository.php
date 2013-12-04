<?php
namespace Scalr\Service\Aws\Repository;

use Scalr\Service\Aws\Rds\DataType\DBSecurityGroupData;
use Scalr\Service\Aws\AbstractRepository;

/**
 * RdsDBSecurityGroupRepository
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     22.03.2013
 */
class RdsDBSecurityGroupRepository extends AbstractRepository
{

    /**
     * Reflection class name.
     * @var string
     */
    private static $reflectionClassName = 'Scalr\\Service\\Aws\\Rds\\DataType\\DBSecurityGroupData';

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
        return 'dBSecurityGroupName';
    }

    /**
     * Finds one element in entity manager by the identifier
     *
     * @param    string               $id     dBSecurityGroupName
     * @return   DBSecurityGroupData  Returns DBSecurityGroupData or NULL if nothing found.
     */
    public function find($id)
    {
        return parent::find($id);
    }
}