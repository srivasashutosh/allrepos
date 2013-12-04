<?php
namespace Scalr\Service\Aws\Rds\DataType;

use Scalr\Service\Aws\RdsException;
use Scalr\Service\Aws\Rds\AbstractRdsDataType;
use \DateTime;

/**
 * DBParameterGroupData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    25.03.2013
 */
class DBParameterGroupData extends AbstractRdsDataType
{

    /**
     * Provides the name of the DB Parameter Group.
     *
     * @var string
     */
    public $dBParameterGroupName;

    /**
     * Provides the name of the DB Parameter Group Family
     * that this DB Parameter Group is compatible
     *
     * @var string
     */
    public $dBParameterGroupFamily;

    /**
     * Provides the customer-specified description
     * for this DB Parameter Group
     *
     * @var string
     */
    public $description;

    /**
     * Constructor
     *
     * @param   string     $dBParameterGroupName   Provides the name of the DB Parameter Group.
     * @param   string     $dBParameterGroupFamily Provides the name of the DB Parameter Group Family
     * @param   string     $description            Provides the customer-specified description
     */
    public function __construct($dBParameterGroupName, $dBParameterGroupFamily, $description)
    {
        parent::__construct();
        $this->dBParameterGroupName = $dBParameterGroupName;
        $this->dBParameterGroupFamily = $dBParameterGroupFamily;
        $this->description = $description;
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\Rds.AbstractRdsDataType::throwExceptionIfNotInitialized()
     */
    protected function throwExceptionIfNotInitialized()
    {
        parent::throwExceptionIfNotInitialized();
        if ($this->dBParameterGroupName === null) {
            throw new RdsException(sprintf(
                'dbParameterGroupName has not been initialized for the "%s" yet', get_class($this)
            ));
        }
    }

    /**
     * DescribeDBParameterGroups action
     *
     * Refreshes description of the object using request to Amazon.
     * NOTE! It refreshes object itself only when EntityManager is enabled.
     * If not, solution is to use $object = object->refresh() instead.
     *
     * @return  DBParameterGroupData Returns DBParameterGroupData on success or throws an exception.
     * @throws  ClientException
     * @throws  RdsException
     */
    public function refresh()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getRds()->dbParameterGroup->describe($this->dBParameterGroupName)->get(0);
    }

    /**
     * CreateDBParameterGroup action
     *
     * Creates a new DB Parameter Group.
     * A DB Parameter Group is initially created with the default parameters for the database engine used by
     * the DB Instance. To provide custom values for any of the parameters, you must modify the group after
     * creating it using ModifyDBParameterGroup. Once you've created a DB Parameter Group, you need to
     * associate it with your DB Instance using ModifyDBInstance. When you associate a new DB Parameter
     * Group with a running DB Instance, you need to reboot the DB Instance for the new DB Parameter Group
     * and associated settings to take effect.
     *
     * @return  DBParameterGroupData Returns DBParameterGroupData on success or throws an exception.
     * @throws  ClientException
     * @throws  RdsException
     */
    public function create()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getRds()->dbParameterGroup->create($this);
    }

    /**
     * DeleteDBParameterGroup action
     *
     * Deletes a specified DBParameterGroup. The DBParameterGroup cannot
     * be associated with any RDS instances to be deleted.
     * Note! The specified DB Parameter Group cannot be associated with any DB Instances
     *
     * @return  bool       Returns true on success or throws an exception.
     * @throws  ClientException
     * @throws  RdsException
     */
    public function delete()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getRds()->dbParameterGroup->delete($this->dBParameterGroupName);
    }

    /**
     * ModifyDBParameterGroup action
     *
     * Modifies the parameters of a DBParameterGroup. To modify more than one parameter submit a list of
     * the following: ParameterName, ParameterValue, and ApplyMethod. A maximum of 20 parameters can
     * be modified in a single request.
     *
     * Note! The apply-immediate method can be used only for dynamic parameters; the pending-reboot
     * method can be used with MySQL and Oracle DB Instances for either dynamic or static parameters.
     * For Microsoft SQL Server DB Instances, the pending-reboot method can be used only for
     * static parameters.
     *
     * @param   ParameterList|ParameterData|array $parameters           An list of parameter names, values, and the apply method
     *                                            for the parameter update. At least one parameter name, value,
     *                                            and apply method must be supplied;
     *                                            subsequent arguments are optional.
     *                                            A maximum of 20 parameters may be modified in a single request.
     *                                            Valid Values (for the application method): immediate | pending-reboot
     * @return  string        Returns DBParameterGroupName on success or throws an exception.
     * @throws  ClientException
     * @throws  RdsException
     */
    public function modify($parameters)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getRds()->dbParameterGroup->modify($this->dBParameterGroupName, $parameters);
    }

    /**
     * ResetDBParameterGroup action
     *
     * Modifies the parameters of a DBParameterGroup to the engine/system default value.
     * To reset specific parameters submit a list of the following: ParameterName and ApplyMethod.
     * To reset the entire DBParameterGroup specify the DBParameterGroup name and ResetAllParameters parameters.
     * When resetting the entire group, dynamic parameters are updated immediately and static parameters are set
     * to pending-reboot to take effect on the next DB instance restart or RebootDBInstance request.
     *
     * @param   ParameterList|ParameterData|array   $parameters optional An list of parameter names, values, and the apply method
     *                                              for the parameter update. At least one parameter name, value,
     *                                              and apply method must be supplied;
     *                                              subsequent arguments are optional.
     *                                              A maximum of 20 parameters may be modified in a single request.
     *                                              Valid Values (for the application method): immediate | pending-reboot
     * @param   bool          $resetAllParameters   optional Specifies whether (true) or not (false) to reset all parameters
     *                                              in the DB Parameter Group to default values.
     * @return  string        Returns DBParameterGroupName on success or throws an exception.
     * @throws  ClientException
     * @throws  RdsException
     */
    public function reset($parameters = null, $resetAllParameters = null)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getRds()->dbParameterGroup->reset($this->dBParameterGroupName, $parameters, $resetAllParameters);
    }

    /**
     * DescribeDBParameters action
     *
     * Returns the detailed parameter list for a particular DBParameterGroup.
     *
     * @param   string     $source               optional The parameter types to return.
     * @param   string     $marker               optional An optional pagination token provided by a previous
     *                                           DescribeDBParameterGroups request. If this parameter is specified, the response includes
     *                                           only records beyond the marker, up to the value specified by MaxRecords.
     * @param   int        $maxRecords           optional The maximum number of records to include in the response.
     *                                           If more records exist than the specified MaxRecords value,
     *                                           a pagination token called a marker is included in the response so that the
     *                                           remaining results may be retrieved.
     * @return  ParameterList Returns ParameterList on success or throws an exception.
     * @throws  ClientException
     * @throws  RdsException
     */
    public function describeParameters($source = null, $marker = null, $maxRecords = null)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getRds()->dbParameterGroup->describeParameters($this->dBParameterGroupName, $source, $marker, $maxRecords);
    }
}