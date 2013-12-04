<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * RouteTableAssociationData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    04.04.2013
 */
class RouteTableAssociationData extends AbstractEc2DataType
{

    /**
     * An identifier representing the association between a route table and a subnet.
     *
     * @var string
     */
    public $routeTableAssociationId;

    /**
     * The ID of the route table.
     *
     * @var string
     */
    public $routeTableId;

    /**
     * The ID of the subnet.
     *
     * @var string
     */
    public $subnetId;

    /**
     * Whether this is the main route table.
     *
     * @var bool
     */
    public $main;

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\Ec2.AbstractEc2DataType::throwExceptionIfNotInitialized()
     */
    protected function throwExceptionIfNotInitialized()
    {
        parent::throwExceptionIfNotInitialized();
        if ($this->routeTableAssociationId === null) {
            throw new Ec2Exception(sprintf(
                'routeTableAssociationId has not been initialized for the "%s" yet.',
                get_class($this)
            ));
        }
    }

    /**
     * DisassociateRouteTable action
     *
     * Disassociates a subnet from a route table.
     * After you perform this action, the subnet no longer uses the routes in the route table.
     * Instead, it uses the routes in the VPC's main route table.
     *
     * @return  bool       Returns TRUE on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function disassociate()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->routeTable->disassociate($this->routeTableAssociationId);
    }

    /**
     * ReplaceRouteTableAssociation action
     *
     * Changes the route table associated with a given subnet in a VPC.
     * After you execute this action, the subnet uses the routes
     * in the new route table it's associated with.
     *
     * You can also use this action to change which table is the main route table in the VPC.
     * You just specify the main route table's association ID and the route table that you
     * want to be the new main route table.
     *
     * @param   string     $routeTableId  The ID of the new route table to associate with the subnet.
     * @return  string     Returns the ID of the new association on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function replaceAssociation($routeTableId)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->routeTable->replaceAssociation($routeTableId, $this->routeTableAssociationId);
    }
}