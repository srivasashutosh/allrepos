<?php
namespace Scalr\Service\Aws\CloudFront\DataType;

use Scalr\Service\Aws\CloudFront\AbstractCloudFrontListDataType;

/**
 * TrustedSignerList
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     01.02.2013
 *
 * @property  bool                          $enabled                   Enabled is true if any of the AWS accounts that are listed
 *                                                                     in the TrustedSigners complex type (for the
 *                                                                     default cache behavior or for any other cache
 *                                                                     behaviors) have active CloudFront key pairs. If not, Enabled is false.
 *
 * @method    string                   getDistributionId()  getDistributionId()      Gets an associated distribution ID.
 * @method    TrustedSignerList        setDistributionId()  setDistributionId($id)   Sets an associated distribution ID.
 * @method    bool                     getEnabled()         getEnabled()             Gets an Enabled property.
 * @method    TrustedSignerList        setEnabled()         setEnabled($val)         Sets an Enabled property.
 */
class TrustedSignerList extends AbstractCloudFrontListDataType
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
    protected $_properties = array('enabled');

    /**
     * Constructor
     *
     * @param array|TrustedSignerData  $aListData  TrustedSignerData List
     */
    public function __construct ($aListData = null)
    {
        parent::__construct(
            $aListData,
            'awsAccountNumber',
            'Scalr\\Service\\Aws\\CloudFront\\DataType\\TrustedSignerData'
        );
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'Singers', $member = true)
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
        $top = $xml->createElement('TrustedSigners');
        $xml->appendChild($top);
        $top->appendChild($xml->createElement('Enabled', $this->enabled ? 'true' : 'false'));
        $top->appendChild($xml->createElement('Quantity', $this->count()));
        if ($this->count() > 0) {
            $items = $xml->createElement('Items');
            $top->appendChild($items);
            /* @var $item TrustedSignerData */
            foreach ($this as $item) {
                $item->appendContentToElement($items);
            }
            unset($items);
        }
        return $returnAsDom ? $xml : $xml->saveXML();
    }
}