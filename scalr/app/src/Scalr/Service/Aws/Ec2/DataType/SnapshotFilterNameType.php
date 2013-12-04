<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\DataType\StringType;
use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;

/**
 * SnapshotFilterNameType
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    21.01.2013
 */
class SnapshotFilterNameType extends StringType
{

    /**
     * Filters the response based on a specific tag/value combination
     *
     * To instantiate the object with the tag:Anything we need to use following construction
     * InstanceFilterNameType::tag('Anything');
     */
    const TYPE_TAG_NAME = 'tag:Name';

    /**
     * A description of the snapshot.
     */
    const TYPE_DESCRIPTION = 'description';

    /**
     * The AWS account alias (for example, amazon) that owns the snapshot
     */
    const TYPE_OWNER_ALIAS = 'owner-alias';

    /**
     * The ID of the AWS account that owns the snapshot
     */
    const TYPE_OWNER_ID = 'owner-id';

    /**
     * The progress of the snapshot, as a percentage (for example, 80%).
     */
    const TYPE_PROGRESS = 'progress';

    /**
     * The snapshot from which the volume was created.
     */
    const TYPE_SNAPSHOT_ID = 'snapshot-id';

    /**
     * The time stamp when the snapshot was initiated.
     * Type: DateTime
     */
    const TYPE_START_TIME = 'start-time';

    /**
     * The status of the volume.
     * Valid values: pending | completed | error
     */
    const TYPE_STATUS = 'status';

    /**
     * The key of a tag assigned to the resource.
     * This filter is independent of the tag-value filter.
     * For example, if you use both the filter "tag-key=Purpose" and the filter "tag-value=X",
     * you get any resources assigned both the tag key Purpose (regardless of what the tag's value is),
     * and the tag value X (regardless of what the tag's key is). If you want to list only resources where
     * Purpose is X, see the tag:key filter
     */
    const TYPE_TAG_KEY = 'tag-key';

    /**
     * The value of a tag assigned to the resource. This filter is independent of the tag-key filter.
     */
    const TYPE_TAG_VALUE = 'tag-value';

    /**
     * The volume ID.
     */
    const TYPE_VOLUME_ID = 'volume-id';

    /**
     * The size of the volume, in GiB (for example, 20).
     */
    const TYPE_VOLUME_SIZE= 'volume-size';

    public static function getPrefix()
    {
        return 'TYPE_';
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.StringType::validate()
     */
    protected function validate()
    {
        return preg_match('#^tag\:.+#', $this->value) ?: parent::validate();
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\DataType.StringType::__callstatic()
     */
    public static function __callStatic($name, $args)
    {
        $class = __CLASS__;
        if ($name == 'tag') {
            if (!isset($args[0])) {
                throw new \InvalidArgumentException(sprintf(
                    'Tag name must be provided! Please use %s::tag("symbolic-name")', $class
                ));
            }
            return new $class('tag:' . $args[0]);
        }
        return parent::__callStatic($name, $args);
    }
}