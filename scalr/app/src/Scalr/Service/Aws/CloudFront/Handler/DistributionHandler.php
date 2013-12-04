<?php
namespace Scalr\Service\Aws\CloudFront\Handler;

use Scalr\Service\Aws\CloudFront\DataType\DistributionConfigData;
use Scalr\Service\Aws\CloudFront\DataType\DistributionList;
use Scalr\Service\Aws\CloudFront\DataType\MarkerType;
use Scalr\Service\Aws\CloudFront\DataType\DistributionData;
use Scalr\Service\Aws\DataType\ListDataType;
use Scalr\Service\Aws\Client\ClientException;
use Scalr\Service\Aws\DataType\ErrorData;
use Scalr\Service\Aws\CloudFrontException;
use Scalr\Service\Aws\CloudFront\AbstractCloudFrontHandler;

/**
 * CloudFront Distribution service interface handler
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     04.02.2013
 */
class DistributionHandler extends AbstractCloudFrontHandler
{
    /**
     * Gets DistributionData object from the EntityManager.
     *
     * You should be aware of the fact that the entity manager is turned off by default.
     *
     * @param   string    $distributionId An distributionId.
     * @return  DistributionData|null Returns DistributionData if it does exist in the cache or NULL otherwise.
     */
    public function get($distributionId)
    {
        return $this->getCloudFront()->getEntityManager()->getRepository('CloudFront:Distribution')->find($distributionId);
    }

    /**
     * GET Distribution List action
     *
     * To list the distributions associated with your AWS account.
     *
     * @param   MarkerType       $marker optional The query parameters.
     * @return  DistributionList Returns the list of Distributions.
     * @throws  CloudFrontException
     * @throws  ClientException
     */
    public function describe(MarkerType $marker = null)
    {
        return $this->getCloudFront()->getApiHandler()->describeDistributions($marker);
    }

    /**
     * POST Distribution action
     *
     * This action creates a new download distribution. By default, you can create a combined total of up to 100
     * download and streaming distributions per AWS account
     *
     * @param   DistributionConfigData|string $config distribution config object or xml document
     * @return  DistributionData Returns created distribution.
     * @throws  CloudFrontException
     * @throws  ClientException
     */
    public function create($config)
    {
        return $this->getCloudFront()->getApiHandler()->createDistribution($config);
    }

    /**
     * GET Distribution Config action
     *
     * @param   string     $distributionId  ID of the distribution.
     * @return  DistributionConfigData Returns DistributionConfig object.
     * @throws  CloudFrontException
     * @throws  ClientException
     */
    public function getConfig($distributionId)
    {
        return $this->getCloudFront()->getApiHandler()->getDistributionConfig($distributionId);
    }

    /**
     * PUT Distribution Config action
     *
     * @param   string                        $distributionId ID of the distribution.
     * @param   DistributionConfigData|string $config         Config for distribution. It accepts object or xml document.
     * @param   string                        $eTag           ETag that is retrieved from getDistributionConfig request.
     * @return  DistributionData              Returns DistributionData object.
     * @throws  CloudFrontException
     * @throws  ClientException
     */
    public function setConfig($distributionId, $config, $eTag)
    {
        return $this->getCloudFront()->getApiHandler()->setDistributionConfig($distributionId, $config, $eTag);
    }

    /**
     * GET Distribution action
     *
     * @param   string           $distributionId  ID of the distribution.
     * @return  DistributionData Returns distribution.
     * @throws  CloudFrontException
     * @throws  ClientException
     */
    public function fetch($distributionId)
    {
        return $this->getCloudFront()->getApiHandler()->getDistribution($distributionId);
    }

    /**
     * DELETE Distribution Config action
     *
     * @param   string                        $distributionId ID of the distribution.
     * @param   string                        $eTag           ETag that is retrieved from getDistributionConfig request.
     * @return  bool                          Returns TRUE on success.
     * @throws  CloudFrontException
     * @throws  ClientException
     */
    public function delete($distributionId, $eTag)
    {
        return $this->getCloudFront()->getApiHandler()->deleteDistribution($distributionId, $eTag);
    }
}