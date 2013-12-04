<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * NetworkInterfaceAttachmentData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    01.04.2013
 */
class NetworkInterfaceAttachmentData extends AbstractEc2DataType
{

    /**
     * The ID of the network interface attachment
     *
     * @var string
     */
    public $attachmentId;

    /**
     * The ID of the instance.
     *
     * @var string
     */
    public $instanceId;

    /**
     * @var string
     */
    public $instanceOwnerId;

    /**
     * @var int
     */
    public $deviceIndex;

    /**
     * @var string
     */
    public $status;

    /**
     * @var DateTime
     */
    public $attachTime;

    /**
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