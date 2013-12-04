<?php
namespace Scalr\Service\Aws\Ec2\Handler;

use Scalr\Service\Aws\Ec2\DataType\AddressData;
use Scalr\Service\Aws\Ec2\DataType\AssociateAddressRequestData;
use Scalr\Service\Aws\Ec2\DataType\AddressFilterList;
use Scalr\Service\Aws\Ec2\DataType\AddressFilterData;
use Scalr\Service\Aws\Ec2\DataType\AddressList;
use Scalr\Service\Aws\DataType\ListDataType;
use Scalr\Service\Aws\Client\ClientException;
use Scalr\Service\Aws\DataType\ErrorData;
use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2Handler;

/**
 * Elastic IP Addresses service interface handler
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     29.01.2013
 */
class AddressHandler extends AbstractEc2Handler
{

    /**
     * DescribeAddresses action
     *
     * Describes one or more of the Elastic IP addresses allocated to your account.
     * This action applies to both EC2 and VPC Elastic IP addresses.
     *
     * @param   ListDataType|array|string                 $publicIpList     optional One or more EC2 Elastic IP addresses
     * @param   ListDataType|array|string                 $allocationIdList optional One or more allocation IDs corresponding to the address
     *                                                                      or addresses to describe (VPC addresses only).
     * @param   AddressFilterList|AddressFilterData|array $filter           optional The filter list.
     * @return  AddressList       Returns AddressList on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function describe($publicIpList = null, $allocationIdList = null, $filter = null)
    {
        if ($publicIpList !== null && !($publicIpList instanceof ListDataType)) {
            $publicIpList = new ListDataType($publicIpList);
        }
        if ($allocationIdList !== null && !($allocationIdList instanceof ListDataType)) {
            $allocationIdList = new ListDataType($allocationIdList);
        }
        if ($filter !== null && !($filter instanceof AddressFilterList)) {
            $filter = new AddressFilterList($filter);
        }
        return $this->getEc2()->getApiHandler()->describeAddresses($publicIpList, $allocationIdList, $filter);
    }

    /**
     * AssociateAddress action
     *
     * Associates an Elastic IP address with an instance.
     * This action applies to both EC2 and VPC Elastic IP addresses.
     *
     * EC2: If the IP address is currently assigned to another instance,
     * the IP address is assigned to the new instance.
     *
     * VPC: If the IP address is currently assigned to another instance, Amazon EC2 returns an error unless
     * you specify the AllowReassociation parameter
     *
     * This is an idempotent operation. If you enter it more than once, Amazon EC2 does not return an error
     *
     * @param   AssociateAddressRequestData $request
     * @return  bool|string       Returns associationId (for VPC Elastic IP addresses) or boolean TRUE (for others) on success.
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function associate(AssociateAddressRequestData $request)
    {
        return $this->getEc2()->getApiHandler()->associateAddress($request);
    }

    /**
     * DisassociateAddress action
     *
     * Disassociates an Elastic IP address from the instance it's assigned to.
     * This action applies to both EC2 Elastic IP addresses and VPC Elastic IP addresses.
     * This is an idempotent action. If you enter it more than once, Amazon EC2 does not return an error
     *
     * @param   string       $publicIp      optional The EC2 Elastic IP address.
     *                                      Condition: Required for EC2 Elastic IP addresses
     * @param   string       $associationId optional The association ID corresponding to the VPC Elastic IP Conditional
     *                                      address. Condition: Required for VPC Elastic IP addresses
     * @return  bool         Returns true on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function disassociate($publicIp = null, $associationId = null)
    {
        return $this->getEc2()->getApiHandler()->disassociateAddress($publicIp, $associationId);
    }

    /**
     * AllocateAddress action
     *
     * Acquires an Elastic IP address for use with your instances.
     *
     * @param   string    $domain  optional Set to vpc to allocate the address for use with instances in a VPC.
     *                             Valid values: vpc. Condition: Required when allocating an address for use
     *                             with instances in a VPC
     * @return  AddressData        Returns AddressData on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function allocate($domain = null)
    {
        return $this->getEc2()->getApiHandler()->allocateAddress($domain);
    }

    /**
     * ReleaseAddress action
     *
     * Releases an Elastic IP address allocated to your account.
     * Important! After releasing an Elastic IP address, it is released to the IP address pool and might be unavailable
     * to your account. Make sure to update your DNS records and any servers or devices that communicate with the address.
     *
     * @param   string       $publicIp     optional The EC2 Elastic IP address
     * @param   string       $allocationId optional The allocation ID that AWS provided when you allocated
     *                                              the address for use with Amazon VPC.
     * @return  bool         Returns true on success
     * @throws  ClientException
     * @throws  Ec2Exception
     * @throws  \InvalidArgumentException
     */
    public function release($publicIp = null, $allocationId = null)
    {
        return $this->getEc2()->getApiHandler()->releaseAddress($publicIp, $allocationId);
    }
}