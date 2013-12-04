<?php
namespace Scalr\Service\Aws\Repository;

use Scalr\Service\Aws\Rds\DataType\DBInstanceData;
use Scalr\Service\Aws\AbstractRepository;

/**
 * RdsDBInstanceRepository
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     19.03.2013
 */
class RdsDBInstanceRepository extends AbstractRepository
{

    /**
     * Reflection class name.
     * @var string
     */
    private static $reflectionClassName = 'Scalr\\Service\\Aws\\Rds\\DataType\\DBInstanceData';

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
        return 'dBInstanceIdentifier';
    }

    /**
     * Finds one element in entity manager by the identifier
     *
     * @param    string               $id An dBInstanceIdentifier
     * @return   DBInstanceData       Returns InstanceData or NULL if nothing found.
     */
    public function find($id)
    {
        return parent::find($id);
    }
}