<?php
namespace Scalr\Service\Aws\CloudFront\DataType;

use Scalr\Service\Aws\CloudFrontException;
use Scalr\Service\Aws\CloudFront\AbstractCloudFrontDataType;
use \DateTime;

/**
 * DistributionConfigOriginData
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     04.02.2013
 *
 * @property  \Scalr\Service\Aws\CloudFront\DataType\DistributionS3OriginConfigData $s3OriginConfig      Amazon S3 Origin config
 * @property  \Scalr\Service\Aws\CloudFront\DataType\CustomOriginConfigData         $customOriginConfig  Amazon Custom Origin config
 *
 * @method    string                      getDistributionId()  getDistributionId()      Gets an associated distribution ID.
 * @method    DistributionConfigAliasData setDistributionId()  setDistributionId($id)   sets an associated distribution ID.
 */
class DistributionConfigOriginData extends AbstractCloudFrontDataType
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
    protected $_properties = array('s3OriginConfig', 'customOriginConfig');

    /**
     * Unique identifier for this origin.
     *
     * When you specify the value of TargetOriginId for the default cache
     * behavior or for another cache behavior, you indicate the origin to which
     * you want the cache behavior to route requests by specifying the value
     * of the Id element for that origin.When a request matches the path pattern
     * for that cache behavior, CloudFront routes the request to the specified
     * origin
     *
     * @var string
     */
    public $originId;

    /**
     * Domain name for the origin.
     *
     * Amazon S3 origins: The DNS name of the Amazon S3 bucket from
     * which you want CloudFront to get objects for this origin, for example,
     * myawsbucket.s3.amazonaws.com.
     * Constraints for Amazon S3 origins:
     *  - The bucket name must be between 3 and 63 characters long (inclusive).
     *  - The bucket name must contain only lowercase characters, numbers,
     *    periods, underscores, and dashes.
     *  - The bucket name must not contain adjacent periods.
     *
     * Custom origins: The DNS domain name for the HTTP server from which
     * you want CloudFront to get objects for this origin, for example,
     * www.example.com.
     * Constraints for custom origins:
     *  - DomainName must be a valid DNS name that contains only a-z, A-Z,
     *    0-9, dot (.), hyphen (-), or underscore (_) characters.
     *  - The name cannot exceed 128 characters.
     *
     * @var string
     */
    public $domainName;

    /**
     * Constructor
     *
     * @param   string     $originId   optional
     * @param   string     $domainName optional
     */
    public function __construct($originId = null, $domainName = null)
    {
        $this->originId = $originId;
        $this->domainName = $domainName;
    }

    /**
     * Sets s3OriginConfig
     *
     * @param   DistributionS3OriginConfigData  $s3OriginConfig
     * @return  DistributionConfigOriginData
     */
    public function setS3OriginConfig(DistributionS3OriginConfigData $s3OriginConfig = null)
    {
        return $this->__call(__FUNCTION__, array($s3OriginConfig));
    }

    /**
     * Sets customOriginConfig
     *
     * @param   CustomOriginConfigData  $customOriginConfig
     * @return  DistributionConfigOriginData
     */
    public function setCustomOriginConfig(CustomOriginConfigData $customOriginConfig = null)
    {
        return $this->__call(__FUNCTION__, array($customOriginConfig));
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.AbstractDataType::toXml()
     */
    public function toXml($returnAsDom = false, &$known = null)
    {
        $xml = new \DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        $origin = $xml->createElement('Origin');
        $xml->appendChild($origin);
        $origin->appendChild($xml->createElement('Id', $this->originId));
        $origin->appendChild($xml->createElement('DomainName', $this->domainName));
        if ($this->s3OriginConfig instanceof DistributionS3OriginConfigData) {
            $this->s3OriginConfig->appendContentToElement($origin);
        }
        if ($this->customOriginConfig instanceof CustomOriginConfigData) {
            $this->customOriginConfig->appendContentToElement($origin);
        }
        return $returnAsDom ? $xml : $xml->saveXML();
    }
}