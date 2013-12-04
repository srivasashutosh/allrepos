<?php
namespace Scalr\Service\Aws\Rds\Handler;

use Scalr\Service\Aws\Rds\DataType\ParameterData;
use Scalr\Service\Aws\Rds\DataType\ParameterList;
use Scalr\Service\Aws\Rds\DataType\DBParameterGroupData;
use Scalr\Service\Aws\Rds\DataType\DBParameterGroupList;
use Scalr\Service\Aws\DataType\ListDataType;
use Scalr\Service\Aws\Client\ClientException;
use Scalr\Service\Aws\DataType\ErrorData;
use Scalr\Service\Aws\RdsException;
use Scalr\Service\Aws\Rds\AbstractRdsHandler;

/**
 * Amazon RDS DbParameterGroupHandler
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     25.03.2013
 */
class DbParameterGroupHandler extends AbstractRdsHandler
{

    /**
     * Gets DbParameterGroupData object from the EntityManager.
     *
     * You should be aware of the fact that the entity manager is turned off by default.
     *
     * @param   string                    $dbParameterGroupName.
     * @return  DbParameterGroupData|null Returns DbParameterGroupData if it does exist in the cache or NULL otherwise.
     */
    public function get($dbParameterGroupName)
    {
        return $this->getRds()->getEntityManager()->getRepository('Rds:DBParameterGroup')->find($dbParameterGroupName);
    }

    /**
     * DescribeDBParameterGroups action
     *
     * Returns a list of DBParameterGroup descriptions.
     * If a DBParameterGroupName is specified, the list will contain only the description of the specified DBParameterGroup.
     *
     * @param   string     $dBParameterGroupName optional The name of a specific DB Parameter Group to return details for.
     * @param   string     $marker               optional An optional pagination token provided by a previous
     *                                           DescribeDBParameterGroups request. If this parameter is specified, the response includes
     *                                           only records beyond the marker, up to the value specified by MaxRecords.
     * @param   int        $maxRecords           optional The maximum number of records to include in the response.
     *                                           If more records exist than the specified MaxRecords value,
     *                                           a pagination token called a marker is included in the response so that the
     *                                           remaining results may be retrieved.
     * @return  DBParameterGroupList             Returns DBParameterGroupList on success or throws an exception.
     * @throws  ClientException
     * @throws  RdsException
     */
    public function describe($dBParameterGroupName = null, $marker = null, $maxRecords = null)
    {
        return $this->getRds()->getApiHandler()->describeDBParameterGroups($dBParameterGroupName, $marker, $maxRecords);
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
     * @param   DBParameterGroupData $request DBParameterGroupData object to create
     * @return  DBParameterGroupData Returns DBParameterGroupData on success or throws an exception.
     * @throws  ClientException
     * @throws  RdsException
     */
    public function create(DBParameterGroupData $request)
    {
        return $this->getRds()->getApiHandler()->createDBParameterGroup($request);
    }

    /**
     * DeleteDBParameterGroup action
     *
     * Deletes a specified DBParameterGroup. The DBParameterGroup cannot
     * be associated with any RDS instances to be deleted.
     * Note! The specified DB Parameter Group cannot be associated with any DB Instances
     *
     * @param   string     $dBParameterGroupName The name of the DB Parameter Group
     * @return  bool       Returns true on success or throws an exception.
     * @throws  ClientException
     * @throws  RdsException
     */
    public function delete($dBParameterGroupName)
    {
        return $this->getRds()->getApiHandler()->deleteDBParameterGroup($dBParameterGroupName);
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
     * @param   string                            $dBParameterGroupName The name of DB Parameter Group to modify.
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
    public function modify($dBParameterGroupName, $parameters)
    {
        if (!($parameters instanceof ParameterList)) {
            $parameters = new ParameterList($parameters);
        }
        return $this->getRds()->getApiHandler()->modifyDBParameterGroup($dBParameterGroupName, $parameters);
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
     * @param   string                              $dBParameterGroupName The name of DB Parameter Group to modify.
     * @param   ParameterList|ParameterData|array   $parameters           optional An list of parameter names, values, and the apply method
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
    public function reset($dBParameterGroupName, $parameters = null, $resetAllParameters = null)
    {
        if ($parameters !== null && !($parameters instanceof ParameterList)) {
            $parameters = new ParameterList($parameters);
        }
        return $this->getRds()->getApiHandler()->resetDBParameterGroup($dBParameterGroupName, $parameters, $resetAllParameters);
    }

    /**
     * DescribeDBParameters action
     *
     * Returns the detailed parameter list for a particular DBParameterGroup.
     *
     * @param   string     $dBParameterGroupName The name of the DB Parameter Group.
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
    public function describeParameters($dBParameterGroupName, $source = null, $marker = null, $maxRecords = null)
    {
        return $this->getRds()->getApiHandler()->describeDBParameters($dBParameterGroupName, $source, $marker, $maxRecords);
    }
}