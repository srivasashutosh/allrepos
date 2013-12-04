<?php
namespace Scalr\Service\Aws\CloudFront\DataType;

use Scalr\Service\Aws\CloudFrontException;
use Scalr\Service\Aws\CloudFront\AbstractCloudFrontDataType;
use \DateTime;

/**
 * KeyPairData
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     01.02.2013
 *
 * @method    string                   getDistributionId()    getDistributionId()        Gets an associated distribution ID.
 * @method    KeyPairsSetData          setDistributionId()    setDistributionId($id)     Sets an associated distribution ID.
 * @method    string                   getAwsAccountNumber()  getAwsAccountNumber()      Gets an associated AWS Account Number.
 * @method    KeyPairsSetData          setAwsAccountNumber()  setAwsAccountNumber($id)   Sets an associated AWS Account Number.
 */
class KeyPairData extends AbstractCloudFrontDataType
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
     * Active key pair associated with AwsAccountNumber
     *
     * @var string
     */
    public $keyPairId;
}