<?php
namespace Scalr\Service\Aws\Ec2\Handler;

use Scalr\Service\Aws\Ec2\DataType\PlacementGroupFilterData;
use Scalr\Service\Aws\Ec2\DataType\PlacementGroupFilterList;
use Scalr\Service\Aws\Ec2\DataType\PlacementGroupList;
use Scalr\Service\Aws\Ec2\DataType\PlacementGroupData;
use Scalr\Service\Aws\DataType\ListDataType;
use Scalr\Service\Aws\Client\ClientException;
use Scalr\Service\Aws\DataType\ErrorData;
use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2Handler;

/**
 * EC2 PlacementGroupHandler service interface handler
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     31.01.2013
 */
class PlacementGroupHandler extends AbstractEc2Handler
{

    /**
     * Gets SubnetData object from the EntityManager.
     * You should be aware of the fact that the entity manager is turned off by default.
     *
     * @param   string          $groupName  An unique identifier.
     * @return  PlacementGroupData|null Returns PlacementGroupData if it does exist in the cache or NULL otherwise.
     */
    public function get($groupName)
    {
        return $this->getEc2()->getEntityManager()->getRepository('Ec2:PlacementGroup')->find($groupName);
    }

    /**
     * DescribePlacementGroups action
     *
     * Describes one or more of your placement groups.
     *
     * @param   ListDataType|array|string                               $groupNameList optional One or more placement group names.
     * @param   PlacementGroupFilterList|PlacementGroupFilterData|array $filter        optional The list of the filters.
     * @return  PlacementGroupList  Returns the list of the PlacementGroupData objects on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function describe($groupNameList = null, $filter = null)
    {
        if ($groupNameList !== null && !($groupNameList instanceof ListDataType)) {
            $groupNameList = new ListDataType($groupNameList);
        }
        if ($filter !== null && !($filter instanceof PlacementGroupFilterList)) {
            $filter = new PlacementGroupFilterList($filter);
        }
        return $this->getEc2()->getApiHandler()->describePlacementGroups($groupNameList, $filter);
    }

    /**
     * CreatePlacementGroup action
     *
     * Creates a placement group that you launch cluster instances into.You must give the group a name unique
     * within the scope of your account.
     *
     * @param   string       $groupName The name of the placement group.
     * @param   string       $strategy  optional The placement group strategy.
     * @return  bool         Returns true on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function create($groupName, $strategy = 'cluster')
    {
        return $this->getEc2()->getApiHandler()->createPlacementGroup($groupName, $strategy);
    }

    /**
     * DeletePlacementGroup action
     *
     * Deletes a placement group from your account.You must terminate all instances in the placement group
     * before deleting it.
     *
     * @param   string       $groupName   The name of the placement group.
     * @return  bool         Returns true on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function delete($groupName)
    {
        return $this->getEc2()->getApiHandler()->deletePlacementGroup($groupName);
    }
}