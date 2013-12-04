<?php
namespace Scalr\Service\Aws\CloudFront\V20120701;

use Scalr\Service\Aws\CloudFront\DataType\WhitelistedCookieNamesData;
use Scalr\Service\Aws\CloudFront\DataType\WhitelistedCookieNamesList;
use Scalr\Service\Aws\CloudFront\DataType\ForwardedValuesCookiesData;
use Scalr\Service\Aws\CloudFront\DataType\DistributionS3OriginConfigData;
use Scalr\Service\Aws\CloudFront\DataType\CustomOriginConfigData;
use Scalr\Service\Aws\CloudFront\DataType\ForwardedValuesData;
use Scalr\Service\Aws\CloudFront\DataType\DistributionConfigOriginData;
use Scalr\Service\Aws\CloudFront\DataType\DistributionConfigOriginList;
use Scalr\Service\Aws\CloudFront\DataType\DistributionConfigLoggingData;
use Scalr\Service\Aws\CloudFront\DataType\CacheBehaviorData;
use Scalr\Service\Aws\CloudFront\DataType\CacheBehaviorList;
use Scalr\Service\Aws\CloudFront\DataType\DistributionConfigAliasData;
use Scalr\Service\Aws\CloudFront\DataType\DistributionConfigAliasList;
use Scalr\Service\Aws\CloudFront\DataType\DistributionConfigData;
use Scalr\Service\Aws\CloudFront\DataType\KeyPairData;
use Scalr\Service\Aws\CloudFront\DataType\KeyPairList;
use Scalr\Service\Aws\CloudFront\DataType\TrustedSignerData;
use Scalr\Service\Aws\CloudFront\DataType\TrustedSignerList;
use Scalr\Service\Aws\CloudFront\DataType\DistributionData;
use Scalr\Service\Aws\CloudFrontException;
use Scalr\Service\Aws\CloudFront\DataType\DistributionList;
use Scalr\Service\Aws\CloudFront\DataType\MarkerType;
use Scalr\Service\Aws\AbstractApi;
use Scalr\Service\Aws\Client\ClientException;
use Scalr\Service\Aws\Client\QueryClient\CloudFrontQueryClient;
use Scalr\Service\Aws\CloudFront;
use Scalr\Service\Aws\EntityManager;
use Scalr\Service\Aws\DataType\ErrorData;
use Scalr\Service\Aws\DataType\ListDataType;
use \DateTime;
use \DateTimeZone;

/**
 * CloudFront Api messaging.
 *
 * Implements CloudFront Low-Level API Actions.
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     01.02.2013
 */
class CloudFrontApi extends AbstractApi
{

    /**
     * @var CloudFront
     */
    protected $cloudFront;

    /**
     * Constructor
     *
     * @param   CloudFront            $cloudFront   CloudFront instance
     * @param   CloudFrontQueryClient $client       Client Interface
     */
    public function __construct(CloudFront $cloudFront, CloudFrontQueryClient $client)
    {
        $this->cloudFront = $cloudFront;
        $this->client = $client;
    }

    /**
     * Gets an EntityManager
     *
     * @return \Scalr\Service\Aws\EntityManager
     */
    public function getEntityManager()
    {
        return $this->cloudFront->getEntityManager();
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
    public function describeDistributions(MarkerType $marker = null)
    {
        $result = null;
        $options = array();
        $aQueryString = array();
        if ($marker !== null) {
            if ($marker->marker !== null) {
                $aQueryString[] = 'Marker=' . self::escape($marker->marker);
            }
            if ($marker->maxItems !== null) {
                $aQueryString[] = 'MaxItems=' . self::escape($marker->maxItems);
            }
        }
        $response = $this->client->call('GET', $options, '/distribution' . (!empty($aQueryString) ? '?' . join('&', $aQueryString) : ''));
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            $result = new DistributionList();
            $result->setCloudFront($this->cloudFront);
            $result->setMarker($this->exist($sxml->Marker) ? (string)$sxml->Marker : null);
            $result->setMaxItems($this->exist($sxml->MaxItems) ? (int)$sxml->MaxItems : null);
            $result->setIsTruncated($this->exist($sxml->IsTruncated) ? ((string)$sxml->IsTruncated == 'true') : null);
            if (!empty($sxml->Items->DistributionSummary)) {
                foreach ($sxml->Items->DistributionSummary as $v) {
                    $item = $this->_loadDistributionData($v);
                    $result->append($item);
                    unset($item);
                }
            }
        }
        return $result;
    }

    /**
     * Loads DistributionData from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  DistributionData Returns DistributionData
     */
    protected function _loadDistributionData(\SimpleXMLElement $v)
    {
        $item = null;
        if ($this->exist($v)) {
            $distributionId = (string)$v->Id;
            $item = $this->cloudFront->distribution->get($distributionId);
            if ($item === null) {
                $item = new DistributionData();
                $bAttach = true;
            } else {
                $item->resetObject();
                $bAttach = false;
            }
            $item->setCloudFront($this->cloudFront);
            $item
                ->setDistributionId($distributionId)
                ->setActiveTrustedSigners($this->_loadTrustedSignerList($v->ActiveTrustedSigners))
                ->setDistributionConfig($this->_loadDistributionConfigData($this->exist($v->DistributionConfig) ? $v->DistributionConfig : $v))
                ->setDomainName($this->exist($v->DomainName) ? (string) $v->DomainName : null)
                ->setInProgressInvalidationBatches($this->exist($v->InProgressInvalidationBatches) ? (string) $v->InProgressInvalidationBatches : null)
                ->setLastModifiedTime($this->exist($v->LastModifiedTime) ? new DateTime((string)$v->LastModifiedTime, new DateTimeZone('UTC')) : null)
                ->setStatus((string)$v->Status)
            ;
            if ($bAttach) {
                $this->getEntityManager()->attach($item);
            }
        }
        return $item;
    }

    /**
     * Parses TrustedSignerList
     *
     * @param   \SimpleXMLElement $sxml
     * @return  TrustedSignerList Returns TrustedSignerList object
     */
    protected function _loadTrustedSignerList(\SimpleXMLElement $sxml)
    {
        $list = new TrustedSignerList();
        $list->setCloudFront($this->cloudFront);
        $list->setEnabled($this->exist($sxml->Enabled) ? ((string)$sxml->Enabled == 'true') : null);
        if (!empty($sxml->Items->Singer)) {
            foreach ($sxml->Items->Singer as $v) {
                $item = new TrustedSignerData();
                $item->awsAccountNumber = (string) $v->AwsAccountNumber;
                $item->setKeyPairIds($this->_loadKeyPairList($v->KeyPairIds));
                $list->append($item);
                unset($item);
            }
        }
        return $list;
    }

    /**
     * Parses KeyPairList
     *
     * @param   \SimpleXMLElement $sxml
     * @return  KeyPairList Returns KeyPairList object
     */
    protected function _loadKeyPairList(\SimpleXMLElement $sxml)
    {
        $list = new KeyPairList();
        $list->setCloudFront($this->cloudFront);
        if (!empty($sxml->Items->KeyPairId)) {
            foreach ($sxml->Items->KeyPairId as $v) {
                $item = new KeyPairData();
                $item->keyPairId = (string) $v->KeyPairId;
                $list->append($item);
                unset($item);
            }
        }
        return $list;
    }

    /**
     * Loads DistributionConfigData from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  DistributionConfigData Returns DistributionConfigData
     */
    protected function _loadDistributionConfigData(\SimpleXMLElement $sxml)
    {
        $item = null;
        if ($this->exist($sxml)) {
            $item = new DistributionConfigData();
            $item->setCloudFront($this->cloudFront);
            $item->aliases = $this->_loadDistributionConfigAliasList($sxml->Aliases);
            $item->cacheBehaviors = $this->_loadCacheBehaviorList($sxml->CacheBehaviors);
            $item->callerReference = $this->exist($sxml->CallerReference) ? (string) $sxml->CallerReference : null;
            $item->comment = $this->exist($sxml->Comment) ? (string) $sxml->Comment : null;
            $item->defaultCacheBehavior = $this->_loadCacheBehaviorData($sxml->DefaultCacheBehavior);
            $item->defaultRootObject = $this->exist($sxml->DefaultRootObject) ? (string) $sxml->DefaultRootObject : null;
            $item->enabled = $this->exist($sxml->Enabled) ? ((string)$sxml->Enabled == 'true') : null;
            $item->logging = $this->_loadDistributionConfigLoggingData($sxml->Logging);
            $item->origins = $this->_loadDistributionConfigOriginList($sxml->Origins);
            $item->priceClass = $this->exist($sxml->PriceClass) ? (string) $sxml->PriceClass : null;
        }
        return $item;
    }

    /**
     * Parses DistributionConfigAliasList
     *
     * @param   \SimpleXMLElement $sxml
     * @return  DistributionConfigAliasList Returns DistributionConfigAliasList object
     */
    protected function _loadDistributionConfigAliasList(\SimpleXMLElement $sxml)
    {
        $list = new DistributionConfigAliasList();
        $list->setCloudFront($this->cloudFront);
        if (!empty($sxml->Items->CNAME)) {
            foreach ($sxml->Items->CNAME as $v) {
                $item = new DistributionConfigAliasData();
                $item->cname = (string) $v;
                $list->append($item);
                unset($item);
            }
        }
        return $list;
    }

    /**
     * Parses CacheBehaviorList
     *
     * @param   \SimpleXMLElement $sxml
     * @return  CacheBehaviorList Returns CacheBehaviorList object
     */
    protected function _loadCacheBehaviorList(\SimpleXMLElement $sxml)
    {
        $list = new CacheBehaviorList();
        $list->setCloudFront($this->cloudFront);
        if (!empty($sxml->Items->CacheBehavior)) {
            foreach ($sxml->Items->CacheBehavior as $v) {
                $item = $this->_loadCacheBehaviorData($v);
                $list->append($item);
                unset($item);
            }
        }
        return $list;
    }

    /**
     * Parses DistributionConfigOriginList
     *
     * @param   \SimpleXMLElement $sxml
     * @return  DistributionConfigOriginList Returns DistributionConfigOriginList object
     */
    protected function _loadDistributionConfigOriginList(\SimpleXMLElement $sxml)
    {
        $list = new DistributionConfigOriginList();
        $list->setCloudFront($this->cloudFront);
        if (!empty($sxml->Items->Origin)) {
            foreach ($sxml->Items->Origin as $v) {
                $item = new DistributionConfigOriginData();
                $item->customOriginConfig = $this->_loadCustomOriginConfigData($v->CustomOriginConfig);
                $item->domainName = $this->exist($v->DomainName) ? (string) $v->DomainName : null;
                $item->originId = (string) $v->Id;
                $item->s3OriginConfig = $this->_loadDistributionS3OriginConfigData($v->S3OriginConfig);
                $list->append($item);
                unset($item);
            }
        }
        return $list;
    }

    /**
     * Loads CustomOriginConfigData from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  CustomOriginConfigData Returns CustomOriginConfigData
     */
    protected function _loadCustomOriginConfigData(\SimpleXMLElement $sxml)
    {
        $item = null;
        if ($this->exist($sxml)) {
            $item = new CustomOriginConfigData();
            $item->setCloudFront($this->cloudFront);
            $item->httpPort = $this->exist($sxml->HTTPPort) ? (int) $sxml->HTTPPort : null;
            $item->httpsPort = $this->exist($sxml->HTTPSPort) ? (int) $sxml->HTTPSPort : null;
            $item->originProtocolPolicy = $this->exist($sxml->OriginProtocolPolicy) ? (string) $sxml->OriginProtocolPolicy : null;
        }
        return $item;
    }

    /**
     * Loads DistributionS3OriginConfigData from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  DistributionS3OriginConfigData Returns DistributionS3OriginConfigData
     */
    protected function _loadDistributionS3OriginConfigData(\SimpleXMLElement $sxml)
    {
        $item = null;
        if ($this->exist($sxml)) {
            $item = new DistributionS3OriginConfigData();
            $item->originAccessIdentity = $this->exist($sxml->OriginAccessIdentity) ? (string) $sxml->OriginAccessIdentity : null;
            $item->setCloudFront($this->cloudFront);
        }
        return $item;
    }

    /**
     * Loads CacheBehaviorData from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  CacheBehaviorData Returns CacheBehaviorData
     */
    protected function _loadCacheBehaviorData(\SimpleXMLElement $sxml)
    {
        $item = null;
        if ($this->exist($sxml)) {
            $item = new CacheBehaviorData();
            $item->setCloudFront($this->cloudFront);
            $item->forwardedValues = $this->_loadForwardedValuesData($sxml->ForwardedValues);
            $item->minTtl = $this->exist($sxml->MinTTL) ? ($sxml->MinTTL - 0) : null;
            $item->pathPattern = $this->exist($sxml->PathPattern) ? (string) $sxml->PathPattern : null;
            $item->targetOriginId = $this->exist($sxml->TargetOriginId) ? (string) $sxml->TargetOriginId : null;
            $item->trustedSigners = $this->_loadTrustedSignerList($sxml->TrustedSigners);
            $item->viewerProtocolPolicy = $this->exist($sxml->ViewerProtocolPolicy) ? (string) $sxml->ViewerProtocolPolicy : null;
        }
        return $item;
    }

    /**
     * Loads ForwardedValuesData from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  ForwardedValuesData Returns ForwardedValuesData
     */
    protected function _loadForwardedValuesData(\SimpleXMLElement $sxml)
    {
        $item = null;
        if ($this->exist($sxml)) {
            $item = new ForwardedValuesData();
            $item->cookies = $this->_loadForwardedValuesCookiesData($sxml->Cookies);
            $item->queryString = $this->exist($sxml->QueryString) ? ((string)$sxml->QueryString == 'true') : null;
            $item->setCloudFront($this->cloudFront);
        }
        return $item;
    }

    /**
     * Loads ForwardedValuesCookiesData from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  ForwardedValuesCookiesData Returns ForwardedValuesCookiesData
     */
    protected function _loadForwardedValuesCookiesData(\SimpleXMLElement $sxml)
    {
        $item = null;
        if ($this->exist($sxml)) {
            $item = new ForwardedValuesCookiesData();
            $item->forward = $this->exist($sxml->Forward) ? (string) $sxml->Forward : null;
            $item->whitelistedNames = $this->_loadWhitelistedCookieNamesList($sxml->WhitelistedNames);
            $item->setCloudFront($this->cloudFront);
        }
        return $item;
    }

    /**
     * Parses WhitelistedCookieNamesList
     *
     * @param   \SimpleXMLElement $sxml
     * @return  WhitelistedCookieNamesList Returns WhitelistedCookieNamesList object
     */
    protected function _loadWhitelistedCookieNamesList(\SimpleXMLElement $sxml)
    {
        $list = new WhitelistedCookieNamesList();
        $list->setCloudFront($this->cloudFront);
        if (!empty($sxml->Items->Name)) {
            foreach ($sxml->Items->Name as $v) {
                $item = new WhitelistedCookieNamesData();
                $item->name = (string) $v;
                $list->append($item);
                unset($item);
            }
        }
        return $list;
    }

    /**
     * Loads DistributionConfigLoggingData from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  DistributionConfigLoggingData Returns DistributionConfigLoggingData
     */
    protected function _loadDistributionConfigLoggingData(\SimpleXMLElement $sxml)
    {
        $item = null;
        if ($this->exist($sxml)) {
            $item = new DistributionConfigLoggingData();
            $item->setCloudFront($this->cloudFront);
            $item->bucket = $this->exist($sxml->Bucket) ? (string) $sxml->Bucket : null;
            $item->enabled = $this->exist($sxml->Enabled) ? ((string) $sxml->Enabled == 'true') : null;
            $item->prefix = $this->exist($sxml->Prefix) ? (string) $sxml->Prefix : null;
            $item->includeCookies = $this->exist($sxml->IncludeCookies) ? ((string) $sxml->IncludeCookies == 'true') : null;
        }
        return $item;
    }

    /**
     * POST Distribution action
     *
     * This action creates a new download distribution. By default, you can create a combined total of up to 100
     * download and streaming distributions per AWS account
     *
     * @param   DistributionConfigData|string $config distribution config object or XML document
     * @return  DistributionData Returns created distribution.
     * @throws  CloudFrontException
     * @throws  ClientException
     */
    public function createDistribution($config)
    {
        $result = null;
        $options = array(
            '_putData' => ($config instanceof DistributionConfigData ? $config->toXml() : (string) $config),
            'Expect'   => '',
        );
        $response = $this->client->call('POST', $options, '/distribution');
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            $result = $this->_loadDistributionData($sxml);
        }
        return $result;
    }

    /**
     * GET Distribution action
     *
     * @param   string           $distributionId  ID of the distribution.
     * @return  DistributionData Returns distribution.
     * @throws  CloudFrontException
     * @throws  ClientException
     */
    public function getDistribution($distributionId)
    {
        $result = null;
        $options = array();
        $response = $this->client->call('GET', $options, sprintf('/distribution/%s', self::escape($distributionId)));
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            $result = $this->_loadDistributionData($sxml);
            $result->setOriginalXml($response->getRawContent());
            $result->setETag($response->getHeader('ETag'));
            $result->distributionConfig->setETag($response->getHeader('ETag'));
        }
        return $result;
    }

    /**
     * GET Distribution Config action
     *
     * @param   string     $distributionId  ID of the distribution.
     * @return  DistributionConfigData Returns DistributionConfig object.
     * @throws  CloudFrontException
     * @throws  ClientException
     */
    public function getDistributionConfig($distributionId)
    {
        $result = null;
        $options = array();
        $response = $this->client->call('GET', $options, sprintf('/distribution/%s/config', self::escape($distributionId)));
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            $result = $this->_loadDistributionConfigData($sxml);
            $result->setOriginalXml($response->getRawContent());
            $result->setETag($response->getHeader('ETag'));
            $entity = $this->cloudFront->distribution->get((string)$distributionId);
            if ($entity !== null) {
                //Refreshes config for the distribution object if it does exist in the storage.
                $entity->distributionConfig = $result;
            }
        }
        return $result;
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
    public function setDistributionConfig($distributionId, $config, $eTag)
    {
        $result = null;
        $options = array(
            'If-Match' => (string) $eTag,
            '_putData' => (($config instanceof DistributionConfigData) ? $config->toXml() : (string)$config),
            'Expect'   => '',
        );
        $response = $this->client->call('PUT', $options, sprintf('/distribution/%s/config', self::escape($distributionId)));
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            $result = $this->_loadDistributionData($sxml);
            $result->setOriginalXml($response->getRawContent());
            $result->setETag($response->getHeader('ETag'));
            $result->distributionConfig->setETag($response->getHeader('ETag'));
        }
        return $result;
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
    public function deleteDistribution($distributionId, $eTag)
    {
        $result = false;
        $options = array(
            'If-Match' => (string) $eTag,
        );
        $response = $this->client->call('DELETE', $options, sprintf('/distribution/%s', self::escape($distributionId)));
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            $entity = $this->cloudFront->distribution->get((string)$distributionId);
            if ($entity !== null) {
                $this->getEntityManager()->detach($entity);
            }
            $result = true;
        }
        return $result;
    }
}