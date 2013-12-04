<?php
namespace Scalr\Service\Aws;

use Scalr\Service\Aws\Client\ClientException;
use Scalr\Service\Aws\DataType\ErrorData;
use Scalr\Service\Aws\DataType\ListDataType;
use Scalr\Service\Aws\Client\QueryClient;
use Scalr\Service\Aws;
use Scalr\Service\Aws\CloudFront\V20120701\CloudFrontApi;

/**
 * Amazon CloudFront interface
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     01.02.2013
 *
 * @property  \Scalr\Service\Aws\CloudFront\Handler\DistributionHandler $distribution  A CloudFront Distribution service interface handler.
 *
 * @method    \Scalr\Service\Aws\CloudFront\V20120701\CloudFrontApi getApiHandler() getApiHandler() Gets a CloudFrontApi handler.
 */
class CloudFront extends AbstractService implements ServiceInterface
{

    /**
     * API Version 20120701
     */
    const API_VERSION_20120701 = '20120701';

    /**
     * Current version of the API
     */
    const API_VERSION_CURRENT = self::API_VERSION_20120701;

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.ServiceInterface::getAllowedEntities()
     */
    public function getAllowedEntities()
    {
        return array('distribution');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.ServiceInterface::getAvailableApiVersions()
     */
    public function getAvailableApiVersions()
    {
        return array(self::API_VERSION_20120701);
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.ServiceInterface::getCurrentApiVersion()
     */
    public function getCurrentApiVersion()
    {
        return self::API_VERSION_CURRENT;
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.ServiceInterface::getUrl()
     */
    public function getUrl()
    {
        return 'cloudfront.amazonaws.com';
    }
}