<?php
namespace Scalr\Service\Aws\Ec2\Handler;

use Scalr\Service\Aws\Ec2\DataType\InternetGatewayFilterData;
use Scalr\Service\Aws\Ec2\DataType\InternetGatewayFilterList;
use Scalr\Service\Aws\Ec2\DataType\InternetGatewayList;
use Scalr\Service\Aws\Ec2\DataType\InternetGatewayData;
use Scalr\Service\Aws\DataType\ListDataType;
use Scalr\Service\Aws\Client\ClientException;
use Scalr\Service\Aws\DataType\ErrorData;
use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2Handler;

/**
 * InternetGatewayHandler
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     03.04.2013
 */
class InternetGatewayHandler extends AbstractEc2Handler
{

    /**
     * Gets InternetGatewayData object from the EntityManager.
     * You should be aware of the fact that the entity manager is turned off by default.
     *
     * @param   string       $internetGatewayId  Unique Identifier.
     * @return  InternetGatewayData|null Returns InternetGatewayData if it does exist in the cache or NULL otherwise.
     */
    public function get($internetGatewayId)
    {
        return $this->getEc2()->getEntityManager()->getRepository('Ec2:InternetGateway')->find($internetGatewayId);
    }

    /**
     * DescribeInternetGateways
     *
     * Describes one or more of your Internet gateways.
     *
     * @param   ListDataType|array|string $internetGatewayIdList optional
     *          The list of Internet gateway identifiers.
     *
     * @param   InternetGatewayFilterList|InternetGatewayFilterData|array $filter optional
     *          The filter list.
     *
     * @return  InternetGatewayList Returns InternetGatewayList on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function describe($internetGatewayIdList = null, $filter = null)
    {
        if ($internetGatewayIdList !== null && !($internetGatewayIdList instanceof ListDataType)) {
            $internetGatewayIdList = new ListDataType($internetGatewayIdList);
        }
        if ($filter !== null && !($filter instanceof InternetGatewayFilterList)) {
            $filter = new InternetGatewayFilterList($filter);
        }
        return $this->getEc2()->getApiHandler()->describeInternetGateways($internetGatewayIdList, $filter);
    }

    /**
     * CreateInternetGateway action
     *
     * Creates a new Internet gateway for use with a VPC
     *
     * @return  InternetGatewayData Returns InternetGatewayData on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function create()
    {
        return $this->getEc2()->getApiHandler()->createInternetGateway();
    }

    /**
     * DeleteInternetGateway action
     *
     * Deletes an Internet gateway from your AWS account.
     * The gateway must not be attached to a VPC
     *
     * @param   string     $internetGatewayId The ID of the Internet Gateway
     * @return  bool       Returns TRUE on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function delete($internetGatewayId)
    {
        return $this->getEc2()->getApiHandler()->deleteInternetGateway($internetGatewayId);
    }

    /**
     * AttachInternetGateway action
     *
     * Attaches an Internet gateway to a VPC, enabling connectivity between the Internet and the VPC
     *
     * @param   string     $internetGatewayId The ID of the internet gateway
     * @param   string     $vpcId             The ID of the VPC
     * @return  bool       Returns TRUE on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function attach($internetGatewayId, $vpcId)
    {
        return $this->getEc2()->getApiHandler()->attachInternetGateway($internetGatewayId, $vpcId);
    }

    /**
     * DetachInternetGateway action
     *
     * Detaches an Internet gateway from a VPC, disabling connectivity between the Internet and the VPC
     *
     * @param   string     $internetGatewayId The ID of the internet gateway
     * @param   string     $vpcId             The ID of the VPC
     * @return  bool       Returns TRUE on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function detach($internetGatewayId, $vpcId)
    {
         return $this->getEc2()->getApiHandler()->detachInternetGateway($internetGatewayId, $vpcId);
    }
}