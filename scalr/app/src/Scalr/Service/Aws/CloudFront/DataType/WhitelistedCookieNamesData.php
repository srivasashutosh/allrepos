<?php
namespace Scalr\Service\Aws\CloudFront\DataType;

use Scalr\Service\Aws\CloudFrontException;
use Scalr\Service\Aws\CloudFront\AbstractCloudFrontDataType;
use \DateTime;

/**
 * WhitelistedCookieNamesData
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     04.02.2013
 *
 * @method    string                     getDistributionId()  getDistributionId()      Gets an associated distribution ID.
 * @method    WhitelistedCookieNamesData setDistributionId()  setDistributionId($id)   sets an associated distribution ID.
 */
class WhitelistedCookieNamesData extends AbstractCloudFrontDataType
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
     * Cookie name
     *
     * @var string
     */
    public $name;

    /**
     * Constructor
     * @param   string      $name optional Cookie name
     */
    public function __construct($name = null)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.AbstractDataType::toXml()
     */
    public function toXml($returnAsDom = false, &$known = null)
    {
        $xml = new \DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        $top = $xml->createElement('Name', $this->name);
        $xml->appendChild($top);
        return $returnAsDom ? $xml : $xml->saveXML();
    }
}