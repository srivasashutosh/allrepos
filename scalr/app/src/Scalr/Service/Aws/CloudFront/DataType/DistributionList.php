<?php
namespace Scalr\Service\Aws\CloudFront\DataType;

use Scalr\Service\Aws\CloudFront\AbstractCloudFrontListDataType;

/**
 * DistributionList
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     01.02.2013
 *
 * @property  string $marker
 * @property  int    $maxItems
 * @property  bool   $isTruncated
 */
class DistributionList extends AbstractCloudFrontListDataType
{

    /**
     * List of external identifier names.
     *
     * @var array
     */
    protected $_externalKeys = array();

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array('marker', 'maxItems', 'isTruncated');

    /**
     * Constructor
     *
     * @param array|DistributionData  $aListData  DistributionData List
     */
    public function __construct ($aListData = null)
    {
        parent::__construct(
            $aListData,
            'distributionId',
            'Scalr\\Service\\Aws\\CloudFront\\DataType\\DistributionData'
        );
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'DistributionId', $member = true)
    {
        return parent::getQueryArray($uriParameterName);
    }
}