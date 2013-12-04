<?php
namespace Scalr\Service\Aws\Ec2\Handler;

use Scalr\Service\Aws\Ec2\DataType\NetworkInterfaceAttributeType;
use Scalr\Service\Aws\Ec2\DataType\CreateNetworkInterfaceRequestData;
use Scalr\Service\Aws\Ec2\DataType\NetworkInterfaceList;
use Scalr\Service\Aws\Ec2\DataType\NetworkInterfaceFilterList;
use Scalr\Service\Aws\Ec2\DataType\NetworkInterfaceData;
use Scalr\Service\Aws\DataType\ListDataType;
use Scalr\Service\Aws\Client\ClientException;
use Scalr\Service\Aws\DataType\ErrorData;
use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2Handler;

/**
 * NetworkInterfaceHandler
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     02.04.2013
 */
class NetworkInterfaceHandler extends AbstractEc2Handler
{

    /**
     * Gets NetworkInterfaceData object from the EntityManager.
     * You should be aware of the fact that the entity manager is turned off by default.
     *
     * @param   string                    $networkInterfaceId  Unique Identifier.
     * @return  NetworkInterfaceData|null Returns NetworkInterfaceData if it does exist in the cache or NULL otherwise.
     */
    public function get($networkInterfaceId)
    {
        return $this->getEc2()->getEntityManager()->getRepository('Ec2:NetworkInterface')->find($networkInterfaceId);
    }

    /**
     * DescribeNetworkInterfaces action
     *
     * Describes one or more of your network interfaces.
     *
     * @param   ListDataType|array|string $networkInterfaceIdList optional
     *          One or more network interface IDs
     *
     * @param   NetworkInterfaceFilterList|NetworkInterfaceFilterData|array $filter optional
     *          Filter list
     *
     * @return  NetworkInterfaceList Returns the list of the Network Interfaces
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function describe($networkInterfaceIdList = null, $filter = null)
    {
        if ($networkInterfaceIdList !== null && !($networkInterfaceIdList instanceof ListDataType)) {
            $networkInterfaceIdList = new ListDataType($networkInterfaceIdList);
        }
        if ($filter !== null && !($filter instanceof NetworkInterfaceFilterList)) {
            $filter = new NetworkInterfaceFilterList($filter);
        }
        return $this->getEc2()->getApiHandler()->describeNetworkInterfaces($networkInterfaceIdList, $filter);
    }

    /**
     * DescribeNetworkInterfaceAttribute action
     *
     * Describes a network interface attribute.You can specify only one attribute at a time.
     *
     * @param   string                               $networkInterfaceId The ID of the network interface.
     * @param   NetworkInterfaceAttributeType|string $attribute          The attribute.
     * @return  mixed  Returns attribute value. It can be string, boolean, NetworkInterfaceAttachmentData or
     *                 GroupList depends on attribute value you provided.
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function describeAttribute($networkInterfaceId, $attribute)
    {
        if (!($attribute instanceof NetworkInterfaceAttributeType)) {
            if (strtolower($attribute) === 'securitygroupid') {
                $attribute = NetworkInterfaceAttributeType::ATTR_GROUP_SET;
            }
            $attribute = new NetworkInterfaceAttributeType($attribute);
        }
        return $this->getEc2()->getApiHandler()->describeNetworkInterfaceAttribute($networkInterfaceId, $attribute);
    }

    /**
     * CreateNetworkInterface action
     *
     * Creates a network interface in the specified subnet
     *
     * @param   CreateNetworkInterfaceRequestData|string $request   Create Request object or ID of the Subnet
     * @return  NetworkInterfaceData Returns created Network Interface
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function create($request)
    {
        if (!($request instanceof CreateNetworkInterfaceRequestData)) {
            $request = new CreateNetworkInterfaceRequestData($request);
        }
        return $this->getEc2()->getApiHandler()->createNetworkInterface($request);
    }

    /**
     * DeleteNetworkInterface action
     *
     * Deletes the specified network interface.
     *
     * @param   string     $networkInterfaceId The ID of the network interface
     * @return  bool Returns true on success or throws an exception
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function delete($networkInterfaceId)
    {
        return $this->getEc2()->getApiHandler()->deleteNetworkInterface($networkInterfaceId);
    }

    /**
     * AttachNetworkInterface action
     *
     * Attaches a network interface to an instance.
     *
     * @param   string       $networkInterfaceId The ID of the network interface
     * @param   string       $instanceId         The ID of the instance to attach to the network interface
     * @param   int          $deviceIndex        The index of the device for the network interface attachment.
     * @return  string  Returns Attachment ID on success or throws an exception
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function attach($networkInterfaceId, $instanceId, $deviceIndex)
    {
        return $this->getEc2()->getApiHandler()->attachNetworkInterface($networkInterfaceId, $instanceId, $deviceIndex);
    }

    /**
     * DetachNetworkInterface
     *
     * Detaches a network interface from an instance
     *
     * @param   string     $attachmentId The ID of the Attachment
     * @param   bool       $force        optional Set to TRUE to force a detachment
     * @return  bool  Returns TRUE on success or throws an exception
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function detach($attachmentId, $force = null)
    {
        return $this->getEc2()->getApiHandler()->detachNetworkInterface($attachmentId, $force);
    }

    /**
     * ModifyNetworkInterfaceAttribute action
     *
     * Modifies a network interface attribute.You can specify only one attribute at a time.
     *
     * @param   string                               $networkInterfaceId The ID of the network interface.
     * @param   NetworkInterfaceAttributeType|string $attr               The attribute name.
     * @param   mixed                                $value              The attribute value.
     * @return  bool  Returns TRUE on success
     * @throws  \BadFunctionCallException
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function modifyAttribute($networkInterfaceId, $attr, $value)
    {
        if (!($attr instanceof NetworkInterfaceAttributeType)) {
            if (strtolower($attr) === 'securitygroupid') {
                $attr = NetworkInterfaceAttributeType::ATTR_GROUP_SET;
            }
            $attr = new NetworkInterfaceAttributeType($attr);
        }
        return $this->getEc2()->getApiHandler()->modifyNetworkInterfaceAttribute($networkInterfaceId, $attr, $value);
    }

    /**
     * ResetNetworkInterfaceAttribute action
     *
     * Resets a network interface attribute.You can specify only one attribute at a time
     *
     * @param   string                               $networkInterfaceId The ID of the network interface.
     * @param   NetworkInterfaceAttributeType|string $attr               The attribute name.
     * @return  bool  Returns TRUE on success
     * @throws  \BadFunctionCallException
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function resetAttribute($networkInterfaceId, $attr)
    {
        if (!($attr instanceof NetworkInterfaceAttributeType)) {
            $attr = new NetworkInterfaceAttributeType($attr);
        }
        return $this->getEc2()->getApiHandler()->resetNetworkInterfaceAttribute($networkInterfaceId, $attr);
    }
}