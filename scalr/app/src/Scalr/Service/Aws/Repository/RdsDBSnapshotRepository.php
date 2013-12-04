<?php
namespace Scalr\Service\Aws\Repository;

use Scalr\Service\Aws\Rds\DataType\DBSnapshotData;
use Scalr\Service\Aws\AbstractRepository;

/**
 * RdsDBSnapshotRepository
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     27.03.2013
 */
class RdsDBSnapshotRepository extends AbstractRepository
{

    /**
     * Reflection class name.
     * @var string
     */
    private static $reflectionClassName = 'Scalr\\Service\\Aws\\Rds\\DataType\\DBSnapshotData';

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
        return 'dBSnapshotIdentifier';
    }

    /**
     * Finds one element in entity manager by the identifier
     *
     * @param    string               $id     dBSnapshotIdentifier
     * @return   DBSnapshotData       Returns DBSnapshotData or NULL if nothing found.
     */
    public function find($id)
    {
        return parent::find($id);
    }
}