<?php
namespace Scalr\Service\Aws\CloudFront\DataType;

use Scalr\Service\Aws\CloudFrontException;
use Scalr\Service\Aws\CloudFront\AbstractCloudFrontDataType;
use \DateTime;

/**
 * DistributionConfigLoggingData
 *
 * A complex type that controls whether access logs are written for the distribution.
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     04.02.2013
 *
 * @method    string                        getDistributionId()  getDistributionId()      Gets an associated distribution ID.
 * @method    DistributionConfigLoggingData setDistributionId()  setDistributionId($id)   sets an associated distribution ID.
 */
class DistributionConfigLoggingData extends AbstractCloudFrontDataType
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
     * Specifies whether you want CloudFront to save access logs to an Amazon S3 bucket
     *
     * @var bool
     */
    public $enabled;

    /**
     * If you want CloudFront to include cookies in access logs, specify true.
     * If you choose to include cookies in logs, CloudFront logs all cookies
     * regardless of whether you configure the distribution to forward all cookies,
     * no cookies, or a specified list of cookies to the origin.
     *
     * @var bool
     */
    public $includeCookies;

    /**
     * The Amazon S3 bucket to store the access logs in, for example,
     * myawslogbucket.s3.amazonaws.com.
     *
     * Constraints: Maximum 128 characters
     *
     * @var string
     */
    public $bucket;

    /**
     * An optional string that you want CloudFront to prefix to the access log
     * filenames for this distribution, for example, myprefix/.
     * If you want to enable logging, but you do not want to specify a prefix, you
     * still must include an empty Prefix element in the Logging element
     *
     * Constraints: Maximum 256 characters; the string must not start with a slash ( / ).
     *
     * @var string
     */
    public $prefix;

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.AbstractDataType::toXml()
     */
    public function toXml($returnAsDom = false, &$known = null)
    {
        $xml = new \DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        $top = $xml->createElement('Logging');
        $xml->appendChild($top);
        $top->appendChild($xml->createElement('Enabled', $this->enabled ? 'true' : 'false'));
        $top->appendChild($xml->createElement('IncludeCookies', $this->includeCookies ? 'true' : 'false'));
        if ($this->bucket !== null) {
            $top->appendChild($xml->createElement('Bucket', $this->bucket));
        } else {
            $top->appendChild($xml->createElement('Bucket'));
        }
        if ($this->prefix !== null) {
            $top->appendChild($xml->createElement('Prefix', $this->prefix));
        } else {
            $top->appendChild($xml->createElement('Prefix'));
        }
        return $returnAsDom ? $xml : $xml->saveXML();
    }
}