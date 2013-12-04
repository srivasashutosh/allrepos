<?php
namespace Scalr\Service\Aws\Repository;

use Scalr\Service\Aws\CloudFront\DataType\DistributionData;
use Scalr\Service\Aws\AbstractRepository;

/**
 * CloudFrontDistributionRepository
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     04.02.2013
 */
class CloudFrontDistributionRepository extends AbstractRepository
{

    /**
     * Reflection class name.
     * @var string
     */
    private static $reflectionClassName = 'Scalr\\Service\\Aws\\CloudFront\\DataType\\DistributionData';

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
        return 'distributionId';
    }

    /**
     * Finds one element in entity manager by unique identifier
     *
     * @param    string             $id     Distribution Id
     * @return   DistributionData   Returns InstanceData or NULL if nothing found.
     */
    public function find($id)
    {
        return parent::find($id);
    }
}