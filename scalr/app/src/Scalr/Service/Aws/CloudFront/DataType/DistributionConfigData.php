<?php
namespace Scalr\Service\Aws\CloudFront\DataType;

use Scalr\Service\Aws\CloudFrontException;
use Scalr\Service\Aws\CloudFront\AbstractCloudFrontDataType;
use \DateTime;

/**
 * DistributionConfigData
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     04.02.2013
 *
 * @property  \Scalr\Service\Aws\CloudFront\DataType\DistributionConfigAliasList   $aliases              The data that contains information about CNAMEs
 * @property  \Scalr\Service\Aws\CloudFront\DataType\DistributionConfigOriginList  $origins              A complex type that describes the Amazon S3 bucket or the HTTP server
 *                                                                                                       (for example, a web server) from which CloudFront gets your files.
 *                                                                                                       You must create at least one origin.
 * @property  \Scalr\Service\Aws\CloudFront\DataType\CacheBehaviorData             $defaultCacheBehavior The default cache behavior.
 * @property  \Scalr\Service\Aws\CloudFront\DataType\CacheBehaviorList             $cacheBehaviors       The cache behavior list.
 * @property  \Scalr\Service\Aws\CloudFront\DataType\DistributionConfigLoggingData $logging              It controls whether access logs are written for the
 *                                                                                                       distribution.
 *
 * @method    string                   getDistributionId()  getDistributionId()      Gets an associated distribution ID.
 * @method    DistributionConfigData   setDistributionId()  setDistributionId($id)   Sets an associated distribution ID.
 * @method    string                   getETag()            getETag()                Gets an ETag.
 * @method    DistributionConfigData   setETag()            setETag($val)            Sets an ETag.
 */
class DistributionConfigData extends AbstractCloudFrontDataType
{

    const PRICE_CLASS_100 = 'PriceClass_100';

    const PRICE_CLASS_200 = 'PriceClass_200';

    const PRICE_CLASS_ALL = 'PriceClass_All';

    /**
     * List of external identifier names.
     *
     * @var array
     */
    protected $_externalKeys = array('distributionId');

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array(
        'aliases', 'origins', 'defaultCacheBehavior', 'cacheBehaviors',
        'logging', 'eTag'
    );

    /**
     * A unique value (for example, a date-time stamp) that ensures that the
     * request can't be replayed.
     *
     * @var string
     */
    public $callerReference;

    /**
     * The object that you want CloudFront to request from your origin
     *
     * @var string
     */
    public $defaultRootObject;

    /**
     * Any comments you want to include about the distribution (up to 128 characters)
     *
     * @var string
     */
    public $comment;

    /**
     * The price class that corresponds with the maximum price that you want
     * to pay for CloudFront service. If you specify PriceClass_All,
     * CloudFront responds to requests for your objects from all CloudFront
     * edge locations.
     * If you specify a price class other than PriceClass_All, CloudFront
     * serves your objects from the CloudFront edge location that has the lowest
     * latency among the edge locations in your price class. Viewers who are
     * in or near regions that are excluded from your specified price class may
     * encounter slower performance.
     *
     * @var string
     */
    public $priceClass;

    /**
     * Whether the distribution is enabled to accept end user requests for content.
     *
     * @var bool
     */
    public $enabled;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->logging = new DistributionConfigLoggingData();
        $this->cacheBehaviors = new CacheBehaviorList();
        $this->callerReference = uniqid();
        $this->aliases = new DistributionConfigAliasList();
        $this->origins = new DistributionConfigOriginList();
    }

    /**
     * Sets Aliases
     *
     * @param   DistributionConfigAliasList|DistributionConfigAliasData|array  $aliases
     * @return  DistributionConfigData
     */
    public function setAliases($aliases)
    {
        if ($aliases !== null && !($aliases instanceof DistributionConfigAliasList)) {
            $aliases = new DistributionConfigAliasList($aliases);
        }
        return $this->__call(__FUNCTION__, array($aliases));
    }

    /**
     * Sets Origins
     *
     * @param   DistributionConfigOriginList|DistributionConfigOriginData|array  $origins
     * @return  DistributionConfigData
     */
    public function setOrigins($origins)
    {
        if ($origins !== null && !($origins instanceof DistributionConfigOriginList)) {
            $origins = new DistributionConfigOriginList($origins);
        }
        return $this->__call(__FUNCTION__, array($origins));
    }

    /**
     * Sets CacheBehaviorData
     *
     * @param   CacheBehaviorData $defaultCacheBehavior
     * @return  DistributionConfigData
     */
    public function setDefaultCacheBehavior(CacheBehaviorData $defaultCacheBehavior = null)
    {
        return $this->__call(__FUNCTION__, array($defaultCacheBehavior));
    }

    /**
     * Sets logging
     *
     * @param   DistributionConfigLoggingData $logging
     * @return  DistributionConfigData
     */
    public function setLogging(DistributionConfigLoggingData $logging = null)
    {
        return $this->__call(__FUNCTION__, array($logging));
    }

    /**
     * Sets CacheBehaviors
     *
     * @param   CacheBehaviorList|CacheBehaviorData|array $cacheBehaviors
     * @return  DistributionConfigData
     */
    public function setCacheBehaviors($cacheBehaviors)
    {
        if ($cacheBehaviors !== null && !($cacheBehaviors instanceof CacheBehaviorList)) {
            $cacheBehaviors = new CacheBehaviorList($cacheBehaviors);
        }
        return $this->__call(__FUNCTION__, array($cacheBehaviors));
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.AbstractDataType::toXml()
     */
    public function toXml($returnAsDom = false, &$known = null)
    {
        $xml = new \DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        $top = $xml->createElementNS('http://cloudfront.amazonaws.com/doc/2012-07-01/', 'DistributionConfig');
        $xml->appendChild($top);

        $top->appendChild($xml->createElement('CallerReference', $this->callerReference));
        if ($this->aliases instanceof DistributionConfigAliasList) {
            $this->aliases->appendContentToElement($top);
        }
        $top->appendChild($xml->createElement('DefaultRootObject', $this->defaultRootObject));
        if ($this->origins instanceof DistributionConfigOriginList) {
            $this->origins->appendContentToElement($top);
        }
        if ($this->defaultCacheBehavior instanceof CacheBehaviorData) {
            //This needs in order to change node name as by default method returns "CacheBehavior" name.
            $dcb = $this->defaultCacheBehavior->toXml(true);
            $newnode = $xml->createElement('DefaultCacheBehavior');
            foreach ($dcb->firstChild->childNodes as $child){
                $newnode->appendChild($xml->importNode($child, true));
            }
            $top->appendChild($newnode);
        }
        if ($this->cacheBehaviors instanceof CacheBehaviorList) {
            $this->cacheBehaviors->appendContentToElement($top);
        }
        $top->appendChild($xml->createElement('Comment', $this->comment));
        if ($this->logging instanceof DistributionConfigLoggingData) {
            $this->logging->appendContentToElement($top);
        }
        $top->appendChild($xml->createElement('PriceClass', $this->priceClass));
        $top->appendChild($xml->createElement('Enabled', $this->enabled ? 'true' : 'false'));

        return $returnAsDom ? $xml : $xml->saveXML();
    }
}