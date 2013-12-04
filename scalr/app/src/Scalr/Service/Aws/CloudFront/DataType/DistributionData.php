<?php
namespace Scalr\Service\Aws\CloudFront\DataType;

use Scalr\Service\Aws\CloudFrontException;
use Scalr\Service\Aws\CloudFront\AbstractCloudFrontDataType;
use \DateTime;

/**
 * DistributionData
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     01.02.2013
 *
 * @property  \Scalr\Service\Aws\CloudFront\DataType\TrustedSignerList      $activeTrustedSigners The Active Trusted Singers data
 * @property  \Scalr\Service\Aws\CloudFront\DataType\DistributionConfigData $distributionConfig   The Distribution Config data
 *
 * @method    string                   getETag()            getETag()                Gets an ETag.
 * @method    DistributionData         setETag()            setETag($val)            Sets an ETag.
 */
class DistributionData extends AbstractCloudFrontDataType
{

    const STATUS_DEPLOYED    = 'Deployed';

    const STATUS_IN_PROGRESS = 'InProgress';

    /**
     * List of external identifier names.
     *
     * @var array
     */
    protected $_externalKeys = array();

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array('activeTrustedSigners', 'distributionConfig', 'eTag');

    /**
     * The identifier for the distribution
     *
     * @var string
     */
    public $distributionId;

    /**
     * Indicates the current status of
     * the distribution. When the status is Deployed, the
     * distribution's information is fully propagated throughout
     * the Amazon CloudFront system.
     * Valid Values: Deployed | InProgress
     *
     * @var string
     */
    public $status;

    /**
     * The number of invalidation batches currently in
     * progress for this distribution
     * Valid Values: 0 | 1 | 2 | 3
     *
     * @var string
     */
    public $inProgressInvalidationBatches;

    /**
     * The date and time the distribution was last modified.
     *
     * @var DateTime
     */
    public $lastModifiedTime;

    /**
     * The domain name corresponding to the distribution,
     * for example, d111111abcdef8.cloudfront.net.
     *
     * @var string
     */
    public $domainName;

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\CloudFront.AbstractCloudFrontDataType::throwExceptionIfNotInitialized()
     */
    protected function throwExceptionIfNotInitialized()
    {
        parent::throwExceptionIfNotInitialized();
        if ($this->distributionId === null) {
            throw new CloudFrontException(sprintf('distributionId has not been initialized for the "%s" yet!', get_class($this)));
        }
    }

    /**
     * GET Distribution Config action
     *
     * Refreshes distribution config data set using API request to Amazon.
     * NOTE! It refreshes object itself only when EntityManager is enabled.
     * Decision is to use $object = object->refreshConfig() instead;
     *
     * @return  DistributionConfigData
     * @throws  CloudFrontException
     * @throws  ClientException
     */
    public function refreshConfig()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getCloudFront()->distribution->getConfig($this->distributionId);
    }

    /**
     * PUT Distribution Config action
     *
     * @param   DistributionConfigData|string $config         Config for distribution. It accepts object or xml document.
     * @param   string                        $eTag           ETag that is retrieved from getDistributionConfig request.
     * @return  DistributionData              Returns DistributionData object.
     * @throws  CloudFrontException
     * @throws  ClientException
     */
    public function setConfig($config, $eTag)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getCloudFront()->distribution->setConfig($this->distributionId, $config, $eTag);
    }

    /**
     * GET Distribution action
     *
     * This method refreshes current object using API request to AWS.
     * NOTE! It refreshes object itself only when EntityManager is enabled.
     * Decision is to use $object = object->refresh() instead;
     *
     * @return  DistributionData
     * @throws  CloudFrontException
     * @throws  ClientException
     */
    public function refresh()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getCloudFront()->distribution->fetch($this->distributionId);
    }

    /**
     * DELETE Distribution Config action
     *
     * @param   string                        $eTag optional ETag that is retrieved from getDistributionConfig request.
     * @return  bool                          Returns true on success
     * @throws  CloudFrontException
     * @throws  ClientException
     */
    public function delete($eTag = null)
    {
        $this->throwExceptionIfNotInitialized();
        if ($eTag === null) {
            $eTag = $this->getETag();
            if ($eTag === null) {
                $object = $this->refresh();
                $eTag = $object->getETag();
            }
        }
        return $this->getCloudFront()->distribution->delete($this->distributionId, $eTag);
    }
}