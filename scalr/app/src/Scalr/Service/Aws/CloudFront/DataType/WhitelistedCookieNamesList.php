<?php
namespace Scalr\Service\Aws\CloudFront\DataType;

use Scalr\Service\Aws\CloudFront\AbstractCloudFrontListDataType;

/**
 * WhitelistedCookieNamesList
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     04.02.2013
 *
 * @method    string                      getDistributionId()  getDistributionId()      Gets an associated distribution ID.
 * @method    WhitelistedCookieNamesList  setDistributionId()  setDistributionId($id)   Sets an associated distribution ID.
 */
class WhitelistedCookieNamesList extends AbstractCloudFrontListDataType
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
     * Constructor
     *
     * @param array|WhitelistedCookieNamesData  $aListData  WhitelistedCookieNamesData List
     */
    public function __construct ($aListData = null)
    {
        parent::__construct(
            $aListData,
            'name',
            'Scalr\\Service\\Aws\\CloudFront\\DataType\\WhitelistedCookieNamesData'
        );
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'WhitelistedNames', $member = true)
    {
        return parent::getQueryArray($uriParameterName);
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.AbstractDataType::toXml()
     */
    public function toXml($returnAsDom = false, &$known = null)
    {
        $xml = new \DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        $top = $xml->createElement('WhitelistedNames');
        $xml->appendChild($top);
        $top->appendChild($xml->createElement('Quantity', $this->count()));
        if ($this->count() > 0) {
            $items = $xml->createElement('Items');
            $top->appendChild($items);
            /* @var $item WhitelistedCookieNamesData */
            foreach ($this as $item) {
                $item->appendContentToElement($items);
            }
        }
        return $returnAsDom ? $xml : $xml->saveXML();
    }
}