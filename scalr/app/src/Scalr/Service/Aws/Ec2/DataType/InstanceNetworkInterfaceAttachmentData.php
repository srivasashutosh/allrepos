<?php

namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;
use \DateTime;

/**
 * InstanceNetworkInterfaceAttachmentData
 *
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    10.01.2013
 */
class InstanceNetworkInterfaceAttachmentData extends AbstractEc2DataType
{

    const STATUS_ATTACHING  = 'attaching';

    const STATUS_ATTACHED   = 'attached';

    const STATUS_DETACHING  = 'detaching';

    const STATUS_DETACHED   = 'detached';

    /**
     * The ID of the network interface attachment (attachmentID)
     * @var string
     */
    public $attachmentId;

    /**
     * The index of the device on the instance for the network interface attachment.
     * @var int
     */
    public $deviceIndex;

    /**
     * The attachment state.
     * attaching | attached | detaching | detached
     * @var string
     */
    public $status;

    /**
     * The time stamp when the attachment initiated
     * @var DateTime
     */
    public $attachTime;

    /**
     * Whether the network interface is deleted when the instance is terminated.
     * @var bool
     */
    public $deleteOnTermination;

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\Ec2.AbstractEc2DataType::throwExceptionIfNotInitialized()
     */
    protected function throwExceptionIfNotInitialized()
    {
        parent::throwExceptionIfNotInitialized();
        if ($this->attachmentId === null) {
            throw new Ec2Exception(sprintf(
                'attachmentId has not been initialized for the "%s" yet.',
                get_class($this)
            ));
        }
    }

    /**
     * DetachNetworkInterface
     *
     * Detaches a network interface from an instance
     *
     * @param   bool       $force        optional Set to TRUE to force a detachment
     * @return  bool       Returns TRUE on success or throws an exception
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function detach($force = null)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->networkInterface->detach($this->attachmentId, $force);
    }
}