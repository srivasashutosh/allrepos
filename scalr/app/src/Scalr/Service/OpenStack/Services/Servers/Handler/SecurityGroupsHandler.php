<?php
namespace Scalr\Service\OpenStack\Services\Servers\Handler;

use Scalr\Service\OpenStack\Services\ServersService;
use Scalr\Service\OpenStack\Services\ServiceHandlerInterface;
use Scalr\Service\OpenStack\Services\AbstractServiceHandler;

/**
 * Servers SecurityGroupsHandler
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    14.12.2012
 *
 * @method   array  list()       list($serverId = null)        Gets the list of Security Groups.
 * @method   object create()     create($name, $description)   Creates a new secrurity group.
 * @method   object get()        get($groupId)                 Gets a specific security group.
 * @method   bool   delete()     delete($groupId)              Deletes a specific security group.
 * @method   object addRule()    addRule($rule)                Adds new rule to security group.
 * @method   bool   deleteRule() deleteRule($ruleId)           Deletes the specific security group rule.
 */
class SecurityGroupsHandler extends AbstractServiceHandler implements ServiceHandlerInterface
{
    /**
     * {@inheritdoc}
     * @see Scalr\Service\OpenStack\Services.ServiceHandlerInterface::getServiceMethodAliases()
     */
    public function getServiceMethodAliases()
    {
        return array(
            'list'       => 'listSecurityGroups',
            'create'     => 'createSecurityGroup',
            'get'        => 'getSecurityGroup',
            'delete'     => 'deleteSecurityGroup',
            'addRule'    => 'addSecurityGroupRule',
            'deleteRule' => 'deleteSecurityGroupRule',
        );
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\OpenStack\Services.AbstractServiceHandler::getService()
     * @return  ServersService
     */
    public function getService()
    {
        return parent::getService();
    }
}