<?php
namespace Scalr\Service\Aws\CloudFront\DataType;

use Scalr\Service\Aws\CloudFront\AbstractCloudFrontListDataType;

/**
 * KeyPairList
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     01.02.2013
 *
 * @method    string                   getDistributionId()    getDistributionId()        Gets an associated distribution ID.
 * @method    KeyPairList              setDistributionId()    setDistributionId($id)     Sets an associated distribution ID.
 * @method    string                   getAwsAccountNumber()  getAwsAccountNumber()      Gets an associated AWS Account Number.
 * @method    KeyPairList              setAwsAccountNumber()  setAwsAccountNumber($id)   Sets an associated AWS Account Number.
 */
class KeyPairList extends AbstractCloudFrontListDataType
{

    /**
     * List of external identifier names.
     *
     * @var array
     */
    protected $_externalKeys = array('distributionId', 'awsAccountNumber');

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
     * @param array|KeyPairData  $aListData  KeyPairData List
     */
    public function __construct ($aListData = null)
    {
        parent::__construct(
            $aListData,
            'keyPairId',
            'Scalr\\Service\\Aws\\CloudFront\\DataType\\KeyPairData'
        );
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'KeyPairIds')
    {
        return parent::getQueryArray($uriParameterName);
    }
}