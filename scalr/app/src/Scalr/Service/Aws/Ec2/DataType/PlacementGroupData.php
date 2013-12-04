<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * PlacementGroupData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    31.01.2013
 */
class PlacementGroupData extends AbstractEc2DataType
{

    const STATE_PENDING = 'pending';

    const STATE_AVAILABLE = 'available';

    const STATE_DELETING = 'deleting';

    const STATE_DELETED = 'deleted';

    /**
     * The name of the placement group.
     *
     * @var string
     */
    public $groupName;

    /**
     * The placement strategy
     * Valid values: cluster
     *
     * @var string
     */
    public $strategy;

    /**
     * The status of the placement group.
     * Valid values: pending | available | deleting | deleted
     *
     * @var string
     */
    public $state;

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\Ec2.AbstractEc2DataType::throwExceptionIfNotInitialized()
     */
    protected function throwExceptionIfNotInitialized()
    {
        parent::throwExceptionIfNotInitialized();
        if ($this->groupName === null) {
            throw new Ec2Exception(sprintf('groupName has not been initialized for the "%s" yet!', get_class($this)));
        }
    }

    /**
     * DescribePlacementGroups action
     *
     * Refreshes current object using API request to the AWS.
     * NOTE! It refreshes object itself only when EntityManager is enabled.
     * Decision is to use $object = object->refresh() instead;
     *
     * @return  PlacementGroupData
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function refresh()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->placementGroup->describe($this->groupName)->get(0);
    }

    /**
     * DeletePlacementGroup action
     *
     * Deletes a placement group from your account.You must terminate all instances in the placement group
     * before deleting it.
     *
     * @return  bool         Returns true on success
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function delete()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getEc2()->placementGroup->delete($this->groupName);
    }
}