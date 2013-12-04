<?php
namespace Scalr\Service\Aws\Repository;

use Scalr\Service\Aws\Ec2\DataType\InternetGatewayData;
use Scalr\Service\Aws\AbstractRepository;

/**
 * Ec2InternetGatewayRepository
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     03.04.2013
 */
class Ec2InternetGatewayRepository extends AbstractRepository
{

    /**
     * Reflection class name.
     * @var string
     */
    private static $reflectionClassName = 'Scalr\\Service\\Aws\\Ec2\\DataType\\InternetGatewayData';

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
        return 'internetGatewayId';
    }

    /**
     * Finds one element in entity manager by its unique Identifier
     *
     * @param    string              $id     InternetGatewayId
     * @return   InternetGatewayData Returns InternetGatewayData or NULL if nothing found.
     */
    public function find($id)
    {
        return parent::find($id);
    }
}