<?php
namespace Scalr\Service\Aws\CloudFront\DataType;

use Scalr\Service\Aws\CloudFrontException;
use Scalr\Service\Aws\CloudFront\AbstractCloudFrontDataType;
use \DateTime;

/**
 * ForwardedValuesCookiesData
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     04.02.2013
 *
 * @property  \Scalr\Service\Aws\CloudFront\DataType\WhitelistedCookieNamesList    $whitelistedNames    Cookie name list. Required if you specify whitelist for the value of Forward: A complex
 *                                                                                                      type that specifies how many different cookies you want CloudFront to
 *                                                                                                      forward to the origin for this cache behavior and, if you want to forward
 *                                                                                                      selected cookies, the names of those cookies.
 *                                                                                                      By default, you can whitelist a maximum of 10 cookie names per
 *                                                                                                      distribution. (If you specify all for the value of the Forward element,
 *                                                                                                      CloudFront forwards all cookies regardless of how many your application
 *                                                                                                      uses.)
 *
 * @method    string                     getDistributionId()  getDistributionId()      Gets an associated distribution ID.
 * @method    ForwardedValuesCookiesData setDistributionId()  setDistributionId($id)   sets an associated distribution ID.
 */
class ForwardedValuesCookiesData extends AbstractCloudFrontDataType
{

    const FORWARD_NONE = 'none';

    const FORWARD_WHITELIST = 'whitelist';

    const FORWARD_ALL = 'all';

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
    protected $_properties = array('whitelistedNames');

    /**
     * Specifies which cookies to forward to the origin for this cache behavior:
     * all, none, or the list of cookies specified in the WhitelistedNames
     * complex type.
     *
     * Amazon S3 doesn't process cookies. When the cache behavior is
     * forwarding requests to an Amazon S3 origin, specify none for the
     * Forward element
     *
     * Valid Values: all | whitelist | none
     *
     * @var string
     */
    public $forward;

    /**
     * Constructor
     *
     * @param   string $forward
     * @param   WhitelistedCookieNamesList|WhitelistedCookieNamesData|array $whitelistedNames
     */
    public function __construct($forward = null, $whitelistedNames = null)
    {
        $this->forward = $forward;
        $this->setWhitelistedNames($whitelistedNames);
    }

    /**
     * Sets whitelistedNames
     *
     * @param   WhitelistedCookieNamesList|WhitelistedCookieNamesData|array $whitelistedNames
     * @return  ForwardedValuesCookiesData
     */
    public function setWhitelistedNames($whitelistedNames = null)
    {
        if ($whitelistedNames !== null && !($whitelistedNames instanceof WhitelistedCookieNamesList)) {
            $whitelistedNames = new WhitelistedCookieNamesList($whitelistedNames);
        }
        return $this->__call(__FUNCTION__, array($whitelistedNames));
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.AbstractDataType::toXml()
     */
    public function toXml($returnAsDom = false, &$known = null)
    {
        $xml = new \DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        $top = $xml->createElement('Cookies');
        $xml->appendChild($top);
        $top->appendChild($xml->createElement('Forward', $this->forward));
        if ($this->forward == self::FORWARD_WHITELIST) {
            if ($this->whitelistedNames instanceof WhitelistedCookieNamesList) {
                $this->whitelistedNames->appendContentToElement($top);
            }
        }
        return $returnAsDom ? $xml : $xml->saveXML();
    }
}