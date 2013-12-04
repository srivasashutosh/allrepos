<?php
namespace Scalr\Service\Aws\Rds\DataType;

use Scalr\Service\Aws\RdsException;
use Scalr\Service\Aws\Rds\AbstractRdsDataType;
use \DateTime;

/**
 * DBParameterGroupStatusData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    11.03.2013
 */
class DBParameterGroupStatusData extends AbstractRdsDataType
{

    /**
     * The name of the DP Parameter Group.
     *
     * @var string
     */
    public $dBParameterGroupName;

    /**
     * The status of parameter updates
     *
     * @var string
     */
    public $parameterApplyStatus;

    /**
     * Constructor
     *
     * @param   string     $dBParameterGroupName optional The name of the DP Parameter Group
     * @param   string     $parameterApplyStatus optional The status of parameter updates
     */
    public function __construct($dBParameterGroupName = null, $parameterApplyStatus = null)
    {
        parent::__construct();
        $this->dBParameterGroupName = $dBParameterGroupName;
        $this->parameterApplyStatus = $parameterApplyStatus;
    }
}