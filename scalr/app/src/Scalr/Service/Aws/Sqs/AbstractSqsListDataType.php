<?php
namespace Scalr\Service\Aws\Sqs;

use Scalr\Service\Aws;
use Scalr\Service\Aws\DataType\ListDataType;

/**
 * AbstractSqsListDataType
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     06.11.2012
 * @method    \Scalr\Service\Aws\Sqs   getSqs()  getSqs() Gets an Amazon Sqs instance.
 * @method    AbstractSqsListDataType  setSqs()  setSqs(\Scalr\Service\Aws\Sqs $sqs) Sets an Amazon Sqs instance.
 */
abstract class AbstractSqsListDataType extends ListDataType
{

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.AbstractDataType::getServiceNames()
     */
    public function getServiceNames()
    {
        return array(Aws::SERVICE_INTERFACE_SQS);
    }
}