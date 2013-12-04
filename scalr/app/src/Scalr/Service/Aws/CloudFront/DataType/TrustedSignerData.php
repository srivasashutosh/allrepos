<?php
namespace Scalr\Service\Aws\CloudFront\DataType;

use Scalr\Service\Aws\CloudFrontException;
use Scalr\Service\Aws\CloudFront\AbstractCloudFrontDataType;
use \DateTime;

/**
 * TrustedSignerData
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     01.02.2013
 *
 * @property  \Scalr\Service\Aws\CloudFront\DataType\KeyPairsList   $keyPairIds   Active key pairs for AwsAccountNumber
 *
 * @method    string                   getDistributionId()  getDistributionId()      Gets an associated distribution ID.
 * @method    TrustedSignerData        setDistributionId()  setDistributionId($id)   sets an associated distribution ID.
 */
class TrustedSignerData extends AbstractCloudFrontDataType
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
    protected $_properties = array('keyPairIds');

    /**
     * An AWS account
     *
     * @var string
     */
    public $awsAccountNumber;

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.AbstractDataType::toXml()
     */
    public function toXml($returnAsDom = false, &$known = null)
    {
        $xml = new \DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        $top = $xml->createElement('AwsAccountNumber', $this->awsAccountNumber);
        //NOTE! keyPairIds are exclueded as they do not use in the requests for the CreateDistribution method
        $xml->appendChild($top);
        return $returnAsDom ? $xml : $xml->saveXML();
    }
}