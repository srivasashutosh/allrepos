<?php
namespace Scalr\Service\Aws\CloudFront\DataType;

use Scalr\Service\Aws\CloudFrontException;
use Scalr\Service\Aws\CloudFront\AbstractCloudFrontDataType;
use \DateTime;

/**
 * CacheBehaviorData
 *
 * A complex type that describes how CloudFront processes requests.
 * You must create at least as many cache behaviors (including the default
 * cache behavior) as you have origins if you want CloudFront to distribute
 * objects from all of the origins. Each cache behavior specifies the one
 * origin from which you want CloudFront to get objects. If you have two
 * origins and only the default cache behavior, the default cache behavior
 * will cause CloudFront to get objects from one of the origins, but the other
 * origin will never be used.
 *
 * By default, you can create a maximum of nine cache behaviors in addition
 * to the default cache behavior.
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     04.02.2013
 *
 * @property  \Scalr\Service\Aws\CloudFront\DataType\ForwardedValuesData       $forwardedValues       Forwarded Values data. Specifies how CloudFront handles
 *                                                                                                    query strings and cookies.
 * @property  \Scalr\Service\Aws\CloudFront\DataType\TrustedSignerList         $trustedSigners        Trusted Singers List. Specifies the AWS accounts, if any,
 *                                                                                                    that you want to allow to create signed URLs for private content.
 *
 * @method    string                   getDistributionId()  getDistributionId()      Gets an associated distribution ID.
 * @method    CacheBehaviorData        setDistributionId()  setDistributionId($id)   sets an associated distribution ID.
 */
class CacheBehaviorData extends AbstractCloudFrontDataType
{

    const VIEWER_PROTOCOL_POLICY_ALLOW_ALL = 'allow-all';

    const VIEWER_PROTOCOL_POLICY_HTTPS = 'https';

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
    protected $_properties = array('forwardedValues', 'trustedSigners');

    /**
     * ID of the origin that the default cache behavior applies to.
     *
     * The value of ID for the origin that you want CloudFront to route requests
     * to when a request matches the path pattern either for a cache behavior
     * or for the default cache behavior.
     *
     * @var string
     */
    public $targetOriginId;

    /**
     * Pattern that specifies files that this cache behavior applies to.
     *
     * The pattern (for example, /images/*.jpg) that specifies which requests
     * you want this cache behavior to apply to. When CloudFront receives an
     * end-user request, the requested path is compared with path patterns in
     * the order in which cache behaviors are listed in the distribution.
     * The path pattern for the default cache behavior is * and cannot be
     * changed. If the request for an object does not match the path pattern for
     * any cache behaviors, CloudFront applies the behavior in the default cache
     * behavior.
     *
     * Constraints: Maximum 255 characters.The name of the object can contain
     * any of the following characters:
     *  - A-Z, a-z
     *  - 0-9
     *  - _ - . * $ / ~ " ' @ : +
     *  - * as a character in the string, specified as \*
     *  - &, passed and returned as &amp;
     *
     * @var string
     */
    public $pathPattern;

    /**
     * Use this element to specify the protocol that users can use to access the
     * files in the origin specified by TargetOriginId when a request matches
     * the path pattern in PathPattern. If you want CloudFront to allow end
     * users to use any available protocol, specify allow-all. If you want
     * CloudFront to require HTTPS, specify https.
     *
     * Caution. The only way to guarantee that your end users retrieve an object
     * that was fetched from the origin using HTTPS is never to use
     * any other protocol to fetch the object. If you have recently
     * changed from HTTP to HTTPS, we recommend that you clear
     * your objects' cache because cached objects are protocol
     * agnostic. That means that an edge location will return an object
     * from the cache regardless of whether the current request
     * protocol matches the protocol used previously.
     *
     * Valid Values: allow-all or https
     *
     * @var string
     */
    public $viewerProtocolPolicy;

    /**
     * Minimum TTL in seconds.
     *
     * The minimum amount of time that you want objects to stay in CloudFront
     * caches before CloudFront queries your origin to see whether the object
     * has been updated.
     *
     * Valid Values: 0 to ~3,153,600,000 (100 years)
     *
     * @var int
     */
    public $minTtl;

    /**
     * Constructor
     *
     * @param   string     $targetOriginId       optional
     * @param   string     $viewerProtocolPolicy optional
     * @param   int        $minTtl               optional
     * @param   string     $pathPattern          optional
     */
    public function __construct($targetOriginId = null, $viewerProtocolPolicy = null, $minTtl = null, $pathPattern = null)
    {
        $this->targetOriginId = $targetOriginId;
        $this->pathPattern = $pathPattern;
        $this->viewerProtocolPolicy = $viewerProtocolPolicy;
        $this->minTtl = $minTtl;
        $this->setForwardedValues(new ForwardedValuesData(false));
        $ts = new TrustedSignerList();
        $ts->setEnabled(false);
        $this->setTrustedSigners($ts);
    }

    /**
     * Sets forwardedValues
     *
     * @param   ForwardedValuesData $forwardedValues
     * @return  CacheBehaviorData
     */
    public function setForwardedValues(ForwardedValuesData $forwardedValues = null)
    {
        return $this->__call(__FUNCTION__, array($forwardedValues));
    }

    /**
     * Sets trustedSigners
     *
     * @param   TrustedSignerList|TrustedSignerData|array $trustedSigners
     * @return  CacheBehaviorData
     */
    public function setTrustedSigners($trustedSigners)
    {
        if ($trustedSigners !== null && !($trustedSigners instanceof TrustedSignerList)) {
            $trustedSigners = new TrustedSignerList($trustedSigners);
        }
        return $this->__call(__FUNCTION__, array($trustedSigners));
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.AbstractDataType::toXml()
     */
    public function toXml($returnAsDom = false, &$known = null)
    {
        $xml = new \DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        $top = $xml->createElement('CacheBehavior');
        $xml->appendChild($top);
        if ($this->pathPattern !== null) {
            $top->appendChild($xml->createElement('PathPattern', $this->pathPattern));
        }
        $top->appendChild($xml->createElement('TargetOriginId', $this->targetOriginId));
        if ($this->forwardedValues instanceof ForwardedValuesData) {
            $this->forwardedValues->appendContentToElement($top);
        }
        if ($this->trustedSigners instanceof TrustedSignerList) {
            $this->trustedSigners->appendContentToElement($top);
        }
        $top->appendChild($xml->createElement('ViewerProtocolPolicy', $this->viewerProtocolPolicy));
        $top->appendChild($xml->createElement('MinTTL', $this->minTtl));

        return $returnAsDom ? $xml : $xml->saveXML();
    }
}