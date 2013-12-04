<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * ResourceTagSetData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    28.12.2012
 */
class ResourceTagSetData extends AbstractEc2DataType
{

    /**
     * The tag key.
     * @var string
     */
    public $key;

    /**
     * The tag value.
     * @var string
     */
    public $value;

    /**
     * Constructor
     *
     * @param   string     $key   optional The key of the tag
     * @param   string     $value optional The value of the tag
     */
    public function __construct($key = null, $value = null)
    {
        parent::__construct();
        $this->key = $key;
        $this->value = $value;
    }

    protected function throwExceptionIfNotInitialized()
    {
        parent::throwExceptionIfNotInitialized();
        if ($this->key === null) {
            throw new Ec2Exception(sprintf('key property has not been initialized for the %s yet', get_class($this)));
        }
    }

    /**
     * DeleteTags action
     *
     * Deletes a current tag from a specific set of resources.
     * This method deletes tag regardless of its value.
     *
     * @param   ListDataType|array|string $resourceIdList The ID of a resource to tag. For example, ami-1a2b3c4d.
     *                                                    You can specify multiple resources to assign the tags to.
     * @return  bool               Returns true on success or throws an exception otherwise
     * @throws  ClientException
     * @throws  Ec2Exception
     */
    public function delete($resourceIdList)
    {
        $this->throwExceptionIfNotInitialized();
        $ret = $this->getEc2()->tag->delete($resourceIdList, new ResourceTagSetData($this->key));
        return $ret;
    }
}