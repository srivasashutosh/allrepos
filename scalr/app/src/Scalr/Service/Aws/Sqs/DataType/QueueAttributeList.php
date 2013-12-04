<?php
namespace Scalr\Service\Aws\Sqs\DataType;

use Scalr\Service\Aws\Sqs\AbstractSqsListDataType;

/**
 * QueueAttributeList
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     06.11.2012
 */
class QueueAttributeList extends AbstractSqsListDataType
{

    /**
     * Constructor
     *
     * @param array|QueueAttributeData  $aListData  QueueAttributeData List
     */
    public function __construct($aListData = null)
    {
        parent::__construct(
            $aListData,
            array('name', 'value'),
            'Scalr\\Service\\Aws\\Sqs\\DataType\\QueueAttributeData'
        );
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.ListDataType::getQueryArray()
     */
    public function getQueryArray($uriParameterName = 'Attribute', $member = true)
    {
        return parent::getQueryArray($uriParameterName, $member);
    }
}