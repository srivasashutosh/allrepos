<?php
namespace Scalr\Service\Aws\Ec2\Handler;

use Scalr\Service\Aws\Ec2\DataType\ResourceTagSetData;
use Scalr\Service\Aws\Ec2\DataType\ResourceTagSetList;
use Scalr\Service\Aws\DataType\ListDataType;
use Scalr\Service\Aws\Client\ClientException;
use Scalr\Service\Aws\DataType\ErrorData;
use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2Handler;

/**
 * TagHandler
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     23.01.2013
 */
class TagHandler extends AbstractEc2Handler
{
    /**
     * CreateTags action
     *
     * Adds or overwrites one or more tags for the specified EC2 resource or resources. Each resource can
     * have a maximum of 10 tags. Each tag consists of a key and optional value. Tag keys must be unique per
     * resource.
     *
     * @param   ListDataType|array|string                   $resourceIdList The ID of a resource to tag. For example, ami-1a2b3c4d.
     *                                                                      You can specify multiple resources to assign the tags to.
     * @param   ResourceTagSetList|ResourceTagSetData|array $tagList        The key/value pair list of the Tags.
     * @return  bool               Returns true on success or throws an exception otherwise
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function create($resourceIdList, $tagList)
    {
        if (!($tagList instanceof ResourceTagSetList)) {
            $tagList = new ResourceTagSetList($tagList);
        }
        if (!($resourceIdList instanceof ListDataType)) {
            $resourceIdList = new ListDataType($resourceIdList);
        }
        return $this->getEc2()->getApiHandler()->createTags($resourceIdList, $tagList);
    }

    /**
     * DeleteTags action
     *
     * Deletes a specific set of tags from a specific set of resources. This call is designed to follow a
     * DescribeTags call. You first determine what tags a resource has, and then you call DeleteTags with
     * the resource ID and the specific tags you want to delete.
     *
     * @param   ListDataType|array|string                   $resourceIdList The ID of a resource to tag. For example, ami-1a2b3c4d.
     *                                                                      You can specify multiple resources to assign the tags to.
     * @param   ResourceTagSetList|ResourceTagSetData|array $tagList        The key/value pair list of the Tags.
     * @return  bool               Returns true on success or throws an exception otherwise
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function delete($resourceIdList, $tagList)
    {
        if (!($tagList instanceof ResourceTagSetList)) {
            $tagList = new ResourceTagSetList($tagList);
        }
        if (!($resourceIdList instanceof ListDataType)) {
            $resourceIdList = new ListDataType($resourceIdList);
        }
        return $this->getEc2()->getApiHandler()->deleteTags($resourceIdList, $tagList);
    }
}