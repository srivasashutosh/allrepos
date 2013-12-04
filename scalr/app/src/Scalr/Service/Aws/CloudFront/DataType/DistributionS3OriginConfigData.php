<?php
namespace Scalr\Service\Aws\CloudFront\DataType;

use Scalr\Service\Aws\CloudFrontException;
use Scalr\Service\Aws\CloudFront\AbstractCloudFrontDataType;
use \DateTime;

/**
 * DistributionS3OriginConfigData
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     04.02.2013
 *
 * @method    string                         getDistributionId()  getDistributionId()      Gets an associated distribution ID.
 * @method    DistributionS3OriginConfigData setDistributionId()  setDistributionId($id)   sets an associated distribution ID.
 */
class DistributionS3OriginConfigData extends AbstractCloudFrontDataType
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
     * The CloudFront origin access identity to associate with the origin. Use
     * an origin access identity to configure the origin so that end users can
     * only access objects in an Amazon S3 bucket through CloudFront.
     * If you want end users to be able to access objects using either the
     * CloudFront URL or the Amazon S3 URL, specify an empty
     * OriginAccessIdentity element.
     * To delete the origin access identity from an existing distribution, update
     * the distribution configuration and include an empty
     * OriginAccessIdentity element.
     * To replace the origin access identity, update the distribution configuration
     * and specify the new origin access identity.
     *
     * Constraints: Must be in format - origin-access-identity/cloudfront/Id
     *
     * @var string
     */
    public $originAccessIdentity;

    /**
     * Constructor
     *
     * @param   string    $originAccessIdentity
     */
    public function __construct($originAccessIdentity = null)
    {
        $this->originAccessIdentity = $originAccessIdentity;
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.AbstractDataType::toXml()
     */
    public function toXml($returnAsDom = false, &$known = null)
    {
        $xml = new \DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        $top = $xml->createElement('S3OriginConfig');
        $xml->appendChild($top);
        $top->appendChild($xml->createElement('OriginAccessIdentity', $this->originAccessIdentity));
        return $returnAsDom ? $xml : $xml->saveXML();
    }
}