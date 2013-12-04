<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\Ec2ListDataType;
use Scalr\Service\Aws\Ec2Exception;

/**
 * KeyPairList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    28.01.2013
 */
class KeyPairList extends Ec2ListDataType
{

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array('requestId');

    /**
     * Constructor
     *
     * @param array|KeyPairData  $aListData List of KeyPairData objects
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, array('keyName','keyFingerprint'), __NAMESPACE__ . '\\KeyPairData');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'KeyPair', $member = true)
    {
        return parent::getQueryArray($uriParameterName, $member);
    }
}