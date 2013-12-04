<?php
namespace Scalr\Service\Aws\CloudFront\DataType;

use Scalr\Service\Aws\CloudFrontException;
use Scalr\Service\Aws\CloudFront\AbstractCloudFrontDataType;
use \DateTime;

/**
 * CustomOriginConfigData
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     04.02.2013
 *
 * @method    string                         getDistributionId()  getDistributionId()      Gets an associated distribution ID.
 * @method    CustomOriginConfigData         setDistributionId()  setDistributionId($id)   sets an associated distribution ID.
 */
class CustomOriginConfigData extends AbstractCloudFrontDataType
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
    protected $_properties = array();

    /**
     * The HTTP port that the custom origin listens on.
     * Valid Values: 80, 443, or 1024-65535 (inclusive)
     *
     * @var int
     */
    public $httpPort;

    /**
     * The HTTPS port that the custom origin listens on
     * Valid Values: 80, 443, or 1024-65535 (inclusive)
     *
     * @var int
     */
    public $httpsPort;

    /**
     * The protocol policy that you want CloudFront to use when fetching objects
     * from your origin server. If you specify http-only, CloudFront only uses
     * HTTP to access the origin. If you specify match-viewer, CloudFront
     * fetches objects from your origin using HTTP or HTTPS, depending on
     * the protocol of the viewer request.
     * Valid Values: http-only | match-viewer
     *
     * @var string
     */
    public $originProtocolPolicy;

    /**
     * Constructor
     *
     * @param   int        $httpPort             optional
     * @param   int        $httpsPort            optional
     * @param   string     $originProtocolPolicy optional
     */
    public function __construct($httpPort = null, $httpsPort = null, $originProtocolPolicy = null)
    {
        $this->httpPort = $httpPort;
        $this->httpsPort = $httpsPort;
        $this->originProtocolPolicy = $originProtocolPolicy;
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.AbstractDataType::toXml()
     */
    public function toXml($returnAsDom = false, &$known = null)
    {
        $xml = new \DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        $top = $xml->createElement('CustomOriginConfig');
        $xml->appendChild($top);
        $top->appendChild($xml->createElement('HTTPPort', $this->httpPort));
        $top->appendChild($xml->createElement('HTTPSPort', $this->httpsPort));
        $top->appendChild($xml->createElement('OriginProtocolPolicy', $this->originProtocolPolicy));
        return $returnAsDom ? $xml : $xml->saveXML();
    }
}