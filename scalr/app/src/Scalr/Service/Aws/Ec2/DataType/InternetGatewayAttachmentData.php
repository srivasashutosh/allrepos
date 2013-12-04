<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * InternetGatewayAttachmentData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    03.04.2013
 *
 * @method   string getInternetGatewayId() getInternetGatewayId()
 *           Gets the ID of the associated Internet Gateway
 *
 * @method   InternetGatewayAttachmentData setInternetGatewayId() setInternetGatewayId($id)
 *           Sets the ID of the associated Internet Gateway
 */
class InternetGatewayAttachmentData extends AbstractEc2DataType
{

    const STATE_AVAILABLE = 'available';

    const STATE_ATTACHING = 'attaching';

    const STATE_ATTACHED = 'attached';

    const STATE_DETACHING = 'detaching';

    const STATE_DETACHED = 'detached';

    /**
     * List of external identifier names.
     *
     * @var array
     */
    protected $_externalKeys = array('internetGatewayId');

    /**
     * The ID of the VPC.
     *
     * @var string
     */
    public $vpcId;

    /**
     * The current state of the attachment
     *
     * @var string
     */
    public $state;

    /**
     * Constructor
     *
     * @param   string     $vpcId optional The ID of the VPC.
     * @param   string     $state optional The current state of the attachment
     */
    public function __construct($vpcId = null, $state = null)
    {
        parent::__construct();
        $this->vpcId = $vpcId;
        $this->state = $state;
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\Ec2.AbstractEc2DataType::throwExceptionIfNotInitialized()
     */
    protected function throwExceptionIfNotInitialized()
    {
        parent::throwExceptionIfNotInitialized();
        if ($this->vpcId === null) {
            throw new Ec2Exception(sprintf('vpcId has not been initialized for the "%s" yet.', get_class($this)));
        }
    }

    /**
     * DetachInternetGateway action
     *
     * Detaches an Internet gateway from a VPC, disabling connectivity between the Internet and the VPC
     *
     * @return  bool       Returns TRUE on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function detach()
    {
        $this->throwExceptionIfNotInitialized();
        if ($this->getInternetGatewayId() === null) {
            throw new Ec2Exception(sprintf('internetGatewayId has not been initialized for the "%s" yet.', get_class($this)));
        }
        return $this->getEc2()->internetGateway->detach($this->getInternetGatewayId(), $this->vpcId);
    }
}