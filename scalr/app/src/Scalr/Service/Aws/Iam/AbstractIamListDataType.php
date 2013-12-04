<?php
namespace Scalr\Service\Aws\Iam;

use Scalr\Service\Aws;
use Scalr\Service\Aws\DataType\ListDataType;

/**
 * AbstractIamListDataType
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     13.11.2012
 * @method    \Scalr\Service\Aws\Iam   getIam()  getIam() Gets an Amazon Iam instance.
 * @method    AbstractIamListDataType  setIam()  setIam(\Scalr\Service\Aws\Iam $iam) Sets an Amazon Iam instance.
 */
abstract class AbstractIamListDataType extends ListDataType
{

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.AbstractDataType::getServiceNames()
     */
    public function getServiceNames()
    {
        return array(Aws::SERVICE_INTERFACE_IAM);
    }
}