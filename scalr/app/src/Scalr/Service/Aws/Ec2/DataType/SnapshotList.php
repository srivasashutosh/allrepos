<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2\Ec2ListDataType;
use Scalr\Service\Aws\Ec2Exception;

/**
 * SnapshotList
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    30.01.2013
 */
class SnapshotList extends Ec2ListDataType
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
     * @param array|SnapshotData  $aListData List of SnapshotData objects
     */
    public function __construct($aListData = null)
    {
        parent::__construct($aListData, 'snapshotId', __NAMESPACE__ . '\\SnapshotData');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'SnapshotId', $member = true)
    {
        return parent::getQueryArray($uriParameterName, $member);
    }
}