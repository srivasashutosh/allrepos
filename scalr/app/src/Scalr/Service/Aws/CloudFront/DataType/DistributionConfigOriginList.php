<?php
namespace Scalr\Service\Aws\CloudFront\DataType;

use Scalr\Service\Aws\CloudFront\AbstractCloudFrontListDataType;

/**
 * DistributionConfigOriginList
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     04.02.2013
 *
 * @method    string                       getDistributionId()  getDistributionId()      Gets an associated distribution ID.
 * @method    DistributionConfigOriginList setDistributionId()  setDistributionId($id)   Sets an associated distribution ID.
 */
class DistributionConfigOriginList extends AbstractCloudFrontListDataType
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
     * @param array|DistributionConfigOriginData  $aListData  DistributionConfigOriginData List
     */
    public function __construct ($aListData = null)
    {
        parent::__construct(
            $aListData,
            'originId',
            'Scalr\\Service\\Aws\\CloudFront\\DataType\\DistributionConfigOriginData'
        );
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'Origins', $member = true)
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
        $origins = $xml->createElement('Origins');
        $xml->appendChild($origins);
        $origins->appendChild($xml->createElement('Quantity', $this->count()));
        if ($this->count() > 0) {
            $items = $xml->createElement('Items');
            $origins->appendChild($items);
            /* @var $item DistributionConfigOriginData */
            foreach ($this as $item) {
                $item->appendContentToElement($items);
            }
            unset($items);
        }
        return $returnAsDom ? $xml : $xml->saveXML();
    }
}