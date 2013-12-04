<?php
namespace Scalr\Service\Aws\Repository;

use Scalr\Service\Aws\Rds\DataType\DBParameterGroupData;
use Scalr\Service\Aws\AbstractRepository;

/**
 * RdsDBParameterGroupRepository
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     25.03.2013
 */
class RdsDBParameterGroupRepository extends AbstractRepository
{

    /**
     * Reflection class name.
     * @var string
     */
    private static $reflectionClassName = 'Scalr\\Service\\Aws\\Rds\\DataType\\DBParameterGroupData';

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
        return 'dBParameterGroupName';
    }

    /**
     * Finds one element in entity manager by the identifier
     *
     * @param    string                $id     dBParameterGroupName
     * @return   DBParameterGroupData  Returns DBParameterGroupData or NULL if nothing found.
     */
    public function find($id)
    {
        return parent::find($id);
    }
}