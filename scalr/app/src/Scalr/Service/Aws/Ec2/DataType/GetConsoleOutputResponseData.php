<?php
namespace Scalr\Service\Aws\Ec2\DataType;

use Scalr\Service\Aws\Ec2Exception;
use Scalr\Service\Aws\Ec2\AbstractEc2DataType;
use \DateTime;

/**
 * GetConsoleOutputResponseData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    31.01.2013
 */
class GetConsoleOutputResponseData extends AbstractEc2DataType
{

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array('requestId');

    /**
     * The ID of the instance.
     *
     * @var string
     */
    public $instanceId;

    /**
     * The time the output was last updated
     *
     * @var DateTime
     */
    public $timestamp;

    /**
     * The console output.
     * Base64 encoded string.
     *
     * @var string
     */
    public $output;
}