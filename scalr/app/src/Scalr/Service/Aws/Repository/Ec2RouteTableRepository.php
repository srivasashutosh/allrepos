<?php
namespace Scalr\Service\Aws\Repository;

use Scalr\Service\Aws\Ec2\DataType\RouteTableData;
use Scalr\Service\Aws\AbstractRepository;

/**
 * Ec2RouteTableRepository
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     05.04.2013
 */
class Ec2RouteTableRepository extends AbstractRepository
{

    /**
     * Reflection class name.
     * @var string
     */
    private static $reflectionClassName = 'Scalr\\Service\\Aws\\Ec2\\DataType\\RouteTableData';

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
        return 'routeTableId';
    }

    /**
     * Finds one element in entity manager by its unique Identifier
     *
     * @param    string              $id     RouteTableId
     * @return   RouteTableData      Returns RouteTableData or NULL if nothing found.
     */
    public function find($id)
    {
        return parent::find($id);
    }
}