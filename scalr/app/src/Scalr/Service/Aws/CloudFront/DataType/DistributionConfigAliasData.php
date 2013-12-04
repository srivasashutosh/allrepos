<?php
namespace Scalr\Service\Aws\CloudFront\DataType;

use Scalr\Service\Aws\CloudFrontException;
use Scalr\Service\Aws\CloudFront\AbstractCloudFrontDataType;
use \DateTime;

/**
 * DistributionConfigAliasData
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     04.02.2013
 *
 * @method    string                      getDistributionId()  getDistributionId()      Gets an associated distribution ID.
 * @method    DistributionConfigAliasData setDistributionId()  setDistributionId($id)   sets an associated distribution ID.
 */
class DistributionConfigAliasData extends AbstractCloudFrontDataType
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
     * A CNAME (alternate domain name) that you want to associate with this distribution.
     *
     * @var string
     */
    public $cname;

    /**
     * Constructor
     *
     * @param   string    $cname  A CNAME that you want to associate with this distribution.
     */
    public function __construct($cname = null)
    {
        $this->cname = $cname;
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.AbstractDataType::toXml()
     */
    public function toXml($returnAsDom = false, &$known = null)
    {
        $xml = new \DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        $xml->appendChild($xml->createElement('CNAME', $this->cname));
        return $returnAsDom ? $xml : $xml->saveXML();
    }
}