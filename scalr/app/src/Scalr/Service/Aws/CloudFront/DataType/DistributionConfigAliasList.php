<?php
namespace Scalr\Service\Aws\CloudFront\DataType;

use Scalr\Service\Aws\CloudFront\AbstractCloudFrontListDataType;

/**
 * DistributionConfigAliasList
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     04.02.2013
 *
 * @method    string                      getDistributionId()  getDistributionId()      Gets an associated distribution ID.
 * @method    DistributionConfigAliasList setDistributionId()  setDistributionId($id)   Sets an associated distribution ID.
 */
class DistributionConfigAliasList extends AbstractCloudFrontListDataType
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
     * @param array|DistributionConfigAliasData  $aListData  DistributionConfigAliasData List
     */
    public function __construct ($aListData = null)
    {
        parent::__construct(
            $aListData,
            'cname',
            'Scalr\\Service\\Aws\\CloudFront\\DataType\\DistributionConfigAliasData'
        );
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'Aliases', $member = true)
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
        $aliases = $xml->createElement('Aliases');
        $xml->appendChild($aliases);
        $aliases->appendChild($xml->createElement('Quantity', $this->count()));
        if ($this->count() > 0) {
            $items = $xml->createElement('Items');
            $aliases->appendChild($items);
            /* @var $item DistributionConfigAliasData */
            foreach ($this as $item) {
                $item->appendContentToElement($items);
            }
            unset($items);
        }
        return $returnAsDom ? $xml : $xml->saveXML();
    }
}