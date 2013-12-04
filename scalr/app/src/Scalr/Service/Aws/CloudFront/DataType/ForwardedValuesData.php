<?php
namespace Scalr\Service\Aws\CloudFront\DataType;

use Scalr\Service\Aws\CloudFrontException;
use Scalr\Service\Aws\CloudFront\AbstractCloudFrontDataType;
use \DateTime;

/**
 * ForwardedValuesData
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     04.02.2013
 * @property  \Scalr\Service\Aws\CloudFront\DataType\ForwardedValuesCookiesData  $cookies Specifies whether you want CloudFront to forward
 *                                                                                        cookies to the origin and, if so, which ones
 * @method    string                   getDistributionId()  getDistributionId()      Gets an associated distribution ID.
 * @method    ForwardedValuesData      setDistributionId()  setDistributionId($id)   sets an associated distribution ID.
 */
class ForwardedValuesData extends AbstractCloudFrontDataType
{

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
    protected $_properties = array('cookies');

    /**
     * Indicates whether you want CloudFront to forward query strings to the
     * origin that is associated with this cache behavior. If so, specify true; if
     * not, specify false.
     *
     * @var bool
     */
    public $queryString;

    /**
     * Constructor
     *
     * @param   bool     $queryString
     */
    public function __construct($queryString = null)
    {
        $this->queryString = $queryString !== null ? (bool)$queryString : null;
        $fv = new ForwardedValuesCookiesData(ForwardedValuesCookiesData::FORWARD_NONE);
        $this->setCookies($fv);
    }

    /**
     * Sets cookies
     *
     * @param   ForwardedValuesCookiesData $trustedSigners
     * @return  CacheBehaviorData
     */
    public function setCookies(ForwardedValuesCookiesData $cookies = null)
    {
        return $this->__call(__FUNCTION__, array($cookies));
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.AbstractDataType::toXml()
     */
    public function toXml($returnAsDom = false, &$known = null)
    {
        $xml = new \DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        $top = $xml->createElement('ForwardedValues');
        $xml->appendChild($top);
        $top->appendChild($xml->createElement('QueryString', ($this->queryString ? 'true' : 'false')));
        if ($this->cookies instanceof ForwardedValuesCookiesData) {
            $this->cookies->appendContentToElement($top);
        }
        return $returnAsDom ? $xml : $xml->saveXML();
    }
}