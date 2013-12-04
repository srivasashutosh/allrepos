<?php
namespace Scalr\Service\Aws\Rds\DataType;

use Scalr\Service\Aws\RdsException;
use Scalr\Service\Aws\Rds\AbstractRdsDataType;
use \DateTime;

/**
 * ParameterData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    26.03.2013
 */
class ParameterData extends AbstractRdsDataType
{

    const APPLY_METHOD_IMMEDIATE = 'immediate';

    const APPLY_METHOD_PENDING_REBOOT = 'pending-reboot';

    const SOURCE_USER = 'user';

    const SOURCE_SYSTEM = 'system';

    const SOURCE_ENGINE_DEFAULT = 'engine-default';

    /**
     * Specifies the valid range of values for the parameter
     *
     * @var string
     */
    public $allowedValues;

    /**
     * Indicates when to apply parameter updates.
     * immediate | pending-reboot
     *
     * @var string
     */
    public $applyMethod;

    /**
     * Specifies the engine specific parameters type
     *
     * @var string
     */
    public $applyType;

    /**
     * Specifies the valid data type for the parameter.
     *
     * @var string
     */
    public $dataType;

    /**
     * Provides a description of the parameter
     *
     * @var string
     */
    public $description;

    /**
     * Indicates whether (true) or not (false) the parameter can be modified.
     * Some parameters have security or operational implications that
     * prevent them from being changed.
     *
     * @var bool
     */
    public $isModifiable;

    /**
     * The earliest engine version to which the parameter can apply.
     *
     * @var string
     */
    public $minimumEngineVersion;

    /**
     * Specifies the name of the parameter
     *
     * @var string
     */
    public $parameterName;

    /**
     * Specifies the value of the parameter
     *
     * @var string
     */
    public $parameterValue;

    /**
     * Indicates the source of the parameter value
     *
     * @var string
     */
    public $source;

    /**
     * Constructor
     *
     * @param   string     $parameterName  optional The name of the parameter.
     * @param   string     $applyMethod    optional Indicates when to apply parameter updates.
     *                                              immediate | pending-reboot
     * @param   string     $parameterValue optional The value of the parameter.
     */
    public function __construct($parameterName = null, $applyMethod = null, $parameterValue = null)
    {
        parent::__construct();
        $this->parameterName = $parameterName;
        $this->parameterValue = $parameterValue;
        $this->applyMethod = $applyMethod;
    }
}