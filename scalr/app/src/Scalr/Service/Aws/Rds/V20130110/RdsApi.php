<?php
namespace Scalr\Service\Aws\Rds\V20130110;

use Scalr\Service\Aws\Rds\DataType\EventData;
use Scalr\Service\Aws\Rds\DataType\EventList;
use Scalr\Service\Aws\Rds\DataType\DescribeEventRequestData;
use Scalr\Service\Aws\Rds\DataType\RestoreDBInstanceFromDBSnapshotRequestData;
use Scalr\Service\Aws\Rds\DataType\DBSnapshotData;
use Scalr\Service\Aws\Rds\DataType\DBSnapshotList;
use Scalr\Service\Aws\Rds\DataType\ParameterData;
use Scalr\Service\Aws\Rds\DataType\ParameterList;
use Scalr\Service\Aws\Rds\DataType\DBParameterGroupData;
use Scalr\Service\Aws\Rds\DataType\DBParameterGroupList;
use Scalr\Service\Aws\Rds\DataType\DBSecurityGroupIngressRequestData;
use Scalr\Service\Aws\Rds\DataType\IPRangeData;
use Scalr\Service\Aws\Rds\DataType\IPRangeList;
use Scalr\Service\Aws\Rds\DataType\EC2SecurityGroupData;
use Scalr\Service\Aws\Rds\DataType\EC2SecurityGroupList;
use Scalr\Service\Aws\Rds\DataType\DBSecurityGroupData;
use Scalr\Service\Aws\Rds\DataType\DBSecurityGroupList;
use Scalr\Service\Aws\Rds\DataType\ModifyDBInstanceRequestData;
use Scalr\Service\Aws\Rds\DataType\VpcSecurityGroupMembershipData;
use Scalr\Service\Aws\Rds\DataType\VpcSecurityGroupMembershipList;
use Scalr\Service\Aws\Rds\DataType\DBSecurityGroupMembershipData;
use Scalr\Service\Aws\Rds\DataType\DBSecurityGroupMembershipList;
use Scalr\Service\Aws\Rds\DataType\DBParameterGroupStatusData;
use Scalr\Service\Aws\Rds\DataType\DBParameterGroupStatusList;
use Scalr\Service\Aws\Rds\DataType\PendingModifiedValuesData;
use Scalr\Service\Aws\Rds\DataType\OptionGroupMembershipData;
use Scalr\Service\Aws\Rds\DataType\EndpointData;
use Scalr\Service\Aws\Rds\DataType\DBInstanceData;
use Scalr\Service\Aws\Rds\DataType\DBInstanceList;
use Scalr\Service\Aws\Rds\DataType\CreateDBInstanceRequestData;
use Scalr\Service\Aws;
use Scalr\Service\Aws\AbstractApi;
use Scalr\Service\Aws\Rds;
use Scalr\Service\Aws\RdsException;
use Scalr\Service\Aws\EntityManager;
use Scalr\Service\Aws\DataType\ErrorData;
use Scalr\Service\Aws\Client\ClientInterface;
use Scalr\Service\Aws\Client\ClientException;
use \DateTimeZone;
use \DateTime;

/**
 * Rds Api messaging.
 *
 * Implements Rds Low-Level API Actions.
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     07.03.2013
 */
class RdsApi extends AbstractApi
{

    const UNEXPECTED = 'Could not %s. Unexpected response from AWS.';

    /**
     * @var Rds
     */
    protected $rds;

    /**
     * @var string
     */
    protected $versiondate;

    /**
     * Constructor
     *
     * @param   Rds                 $rds          Rds instance
     * @param   ClientInterface     $client       Client Interface
     */
    public function __construct(Rds $rds, ClientInterface $client)
    {
        $this->rds = $rds;
        $this->client = $client;
        $this->versiondate = preg_replace('#^.+V(\d{4})(\d{2})(\d{2})$#', '\\1-\\2-\\3', __NAMESPACE__);
    }

    /**
     * Gets an entity manager
     *
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->rds->getEntityManager();
    }

    /**
     * DescribeDBInstances action
     *
     * Returns information about provisioned RDS instances. This API supports pagination
     *
     * @param   string          $dbInstanceIdentifier optional The user-specified instance identifier.
     * @param   string          $marker               optional The response includes only records beyond the marker.
     * @param   int             $maxRecords           optional The maximum number of records to include in the response.
     * @return  DBInstanceList  Returns the list of DB Instances
     * @throws  ClientException
     * @throws  RdsException
     */
    public function describeDBInstances($dbInstanceIdentifier = null, $marker = null, $maxRecords = null)
    {
        $result = null;
        $options = array();
        if ($dbInstanceIdentifier !== null) {
            $options['DBInstanceIdentifier'] = (string) $dbInstanceIdentifier;
        }
        if ($marker !== null) {
            $options['Marker'] = (string) $marker;
        }
        if ($maxRecords !== null) {
            $options['MaxRecords'] = (int) $maxRecords;
        }
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            $result = new DBInstanceList();
            $result->setRds($this->rds);
            $result->marker = $this->exist($sxml->DescribeDBInstancesResult->Marker) ?
                (string) $sxml->DescribeDBInstancesResult->Marker : null;
            if (isset($sxml->DescribeDBInstancesResult->DBInstances->DBInstance)) {
                foreach ($sxml->DescribeDBInstancesResult->DBInstances->DBInstance as $v) {
                    $item = $this->_loadDBInstanceData($v);
                    $result->append($item);
                    unset($item);
                }
            }
        }
        return $result;
    }

    /**
     * Loads DBInstanceData from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  DBInstanceData Returns DBInstanceData
     */
    protected function _loadDBInstanceData(\SimpleXMLElement $sxml)
    {
        $item = null;
        if ($this->exist($sxml)) {
            $dbInstanceIdentifier = (string) $sxml->DBInstanceIdentifier;
            $item = $this->rds->getEntityManagerEnabled() ? $this->rds->dbInstance->get($dbInstanceIdentifier) : null;
            if ($item === null) {
                $item = new DBInstanceData();
                $item->setRds($this->rds);
                $bAttach = true;
            } else {
                $item->resetObject();
                $bAttach = false;
            }

            $item->dBInstanceIdentifier = $dbInstanceIdentifier;
            $item->allocatedStorage = $this->exist($sxml->AllocatedStorage) ? (int) $sxml->AllocatedStorage : null;
            $item->autoMinorVersionUpgrade = $this->exist($sxml->AutoMinorVersionUpgrade) ?
                (((string)$sxml->AutoMinorVersionUpgrade) == 'true') : null;
            $item->availabilityZone = $this->exist($sxml->AvailabilityZone) ? (string) $sxml->AvailabilityZone : null;
            $item->backupRetentionPeriod = $this->exist($sxml->BackupRetentionPeriod) ?
                (int) $sxml->BackupRetentionPeriod : null;
            $item->characterSetName = $this->exist($sxml->CharacterSetName) ? (string) $sxml->CharacterSetName : null;
            $item->dBInstanceClass = $this->exist($sxml->DBInstanceClass) ? (string) $sxml->DBInstanceClass : null;
            $item->dBInstanceStatus = $this->exist($sxml->DBInstanceStatus) ? (string) $sxml->DBInstanceStatus : null;
            $item->dBName = $this->exist($sxml->DBName) ? (string) $sxml->DBName : null;
            $item->engine = $this->exist($sxml->Engine) ? (string) $sxml->Engine : null;
            $item->engineVersion = $this->exist($sxml->EngineVersion) ? (string) $sxml->EngineVersion : null;
            $item->instanceCreateTime = $this->exist($sxml->InstanceCreateTime) ?
                new DateTime((string) $sxml->InstanceCreateTime, new DateTimeZone('UTC')) : null;
            $item->iops = $this->exist($sxml->Iops) ? (int) $sxml->Iops : null;
            $item->latestRestorableTime = $this->exist($sxml->LatestRestorableTime) ?
                new DateTime((string) $sxml->LatestRestorableTime, new DateTimeZone('UTC')) : null;
            $item->licenseModel = $this->exist($sxml->LicenseModel) ? (string) $sxml->LicenseModel : null;
            $item->masterUsername = $this->exist($sxml->MasterUsername) ? (string) $sxml->MasterUsername : null;
            $item->multiAZ = $this->exist($sxml->MultiAZ) ? (((string)$sxml->MultiAZ) == 'true') : null;
            $item->preferredBackupWindow = $this->exist($sxml->PreferredBackupWindow) ?
                (string) $sxml->PreferredBackupWindow : null;
            $item->preferredMaintenanceWindow = $this->exist($sxml->PreferredMaintenanceWindow) ?
                (string) $sxml->PreferredMaintenanceWindow : null;
            $item->publiclyAccessible = $this->exist($sxml->PubliclyAccessible) ?
                (((string)$sxml->PubliclyAccessible) == 'true') : null;
            $item->readReplicaSourceDBInstanceIdentifier = $this->exist($sxml->ReadReplicaSourceDBInstanceIdentifier) ?
                (string) $sxml->ReadReplicaSourceDBInstanceIdentifier : null;
            $item->secondaryAvailabilityZone = $this->exist($sxml->SecondaryAvailabilityZone) ?
                (string) $sxml->SecondaryAvailabilityZone : null;

            $item->readReplicaDBInstanceIdentifiers = array();
            if (!empty($sxml->ReadReplicaDBInstanceIdentifiers->ReadReplicaDBInstanceIdentifier)) {
                foreach ($sxml->ReadReplicaDBInstanceIdentifiers->ReadReplicaDBInstanceIdentifier as $v) {
                    $item->readReplicaDBInstanceIdentifiers[] = (string) $v;
                }
            }
            $item->dBParameterGroups = $this->_loadDBParameterGroupStatusList($sxml->DBParameterGroups);
            $item->dBSecurityGroups = $this->_loadDBSecurityGroupMembershipList($sxml->DBSecurityGroups);
            $item->vpcSecurityGroups = $this->_loadVpcSecurityGroupMembershipList($sxml->VpcSecurityGroups);
            $item->endpoint = $this->_loadEndpointData($sxml->Endpoint);
            $item->optionGroupMembership = $this->_loadOptionGroupMembershipData($sxml->OptionGroupMembership);
            $item->pendingModifiedValues = $this->_loadPendingModifiedValuesData($sxml->PendingModifiedValues);

            if ($bAttach && $this->rds->getEntityManagerEnabled()) {
                $this->getEntityManager()->attach($item);
            }
        }
        return $item;
    }

    /**
     * Loads EndpointData from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  EndpointData Returns EndpointData
     */
    protected function _loadEndpointData(\SimpleXMLElement $sxml)
    {
        $item = null;
        if ($this->exist($sxml)) {
            $item = new EndpointData(
                ($this->exist($sxml->Address) ? (string) $sxml->Address : null),
                ($this->exist($sxml->Port) ? (int) $sxml->Port : null)
            );
            $item->setRds($this->rds);
        }
        return $item;
    }

    /**
     * Loads OptionGroupMembershipData from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  OptionGroupMembershipData Returns OptionGroupMembershipData
     */
    protected function _loadOptionGroupMembershipData(\SimpleXMLElement $sxml)
    {
        $item = null;
        if ($this->exist($sxml)) {
            $item = new OptionGroupMembershipData(
                ($this->exist($sxml->OptionGroupName) ? (string) $sxml->OptionGroupName : null),
                ($this->exist($sxml->Status) ? (string)$sxml->Status : null)
            );
            $item->setRds($this->rds);
        }
        return $item;
    }

    /**
     * Loads PendingModifiedValuesData from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  PendingModifiedValuesData Returns PendingModifiedValuesData
     */
    protected function _loadPendingModifiedValuesData(\SimpleXMLElement $sxml)
    {
        $item = null;
        if ($this->exist($sxml)) {
            $item = new PendingModifiedValuesData();
            $item->setRds($this->rds);
            $item->allocatedStorage = $this->exist($sxml->AllocatedStorage) ? (int)$sxml->AllocatedStorage : null;
            $item->backupRetentionPeriod = $this->exist($sxml->BackupRetentionPeriod) ? (int)$sxml->BackupRetentionPeriod : null;
            $item->dBInstanceClass = $this->exist($sxml->DBInstanceClass) ? (string)$sxml->DBInstanceClass : null;
            $item->dBInstanceIdentifier = $this->exist($sxml->DBInstanceIdentifier) ? (string)$sxml->DBInstanceIdentifier : null;
            $item->engineVersion = $this->exist($sxml->EngineVersion) ? (string)$sxml->EngineVersion : null;
            $item->iops = $this->exist($sxml->Iops) ? (int)$sxml->Iops : null;
            $item->masterUserPassword = $this->exist($sxml->MasterUserPassword) ? (string)$sxml->MasterUserPassword : null;
            $item->multiAZ = $this->exist($sxml->MultiAZ) ? (((string)$sxml->MultiAZ) == 'true') : null;
            $item->port = $this->exist($sxml->Port) ? (int)$sxml->Port : null;
        }
        return $item;
    }

    /**
     * Loads DBParameterGroupStatusList from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  DBParameterGroupStatusList Returns DBParameterGroupStatusList
     */
    protected function _loadDBParameterGroupStatusList(\SimpleXMLElement $sxml)
    {
        $list = new DBParameterGroupStatusList();
        $list->setRds($this->rds);
        if (!empty($sxml->DBParameterGroup)) {
            foreach ($sxml->DBParameterGroup as $v) {
                $item = new DBParameterGroupStatusData(
                    ($this->exist($v->DBParameterGroupName) ? (string)$v->DBParameterGroupName : null),
                    ($this->exist($v->ParameterApplyStatus) ? (string)$v->ParameterApplyStatus : null)
                );
                $item->setRds($this->rds);
                $list->append($item);
                unset($item);
            }
        }
        return $list;
    }

    /**
     * Loads DBSecurityGroupMembershipList from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  DBSecurityGroupMembershipList Returns DBSecurityGroupMembershipList
     */
    protected function _loadDBSecurityGroupMembershipList(\SimpleXMLElement $sxml)
    {
        $list = new DBSecurityGroupMembershipList();
        $list->setRds($this->rds);
        if (!empty($sxml->DBSecurityGroup)) {
            foreach ($sxml->DBSecurityGroup as $v) {
                $item = new DBSecurityGroupMembershipData(
                    ($this->exist($v->DBSecurityGroupName) ? (string)$v->DBSecurityGroupName : null),
                    ($this->exist($v->Status) ? (string)$v->Status : null)
                );
                $item->setRds($this->rds);
                $list->append($item);
                unset($item);
            }
        }
        return $list;
    }

    /**
     * Loads VpcSecurityGroupMembershipList from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  VpcSecurityGroupMembershipList Returns VpcSecurityGroupMembershipList
     */
    protected function _loadVpcSecurityGroupMembershipList(\SimpleXMLElement $sxml)
    {
        $list = new VpcSecurityGroupMembershipList();
        $list->setRds($this->rds);
        if (!empty($sxml->VpcSecurityGroup)) {
            foreach ($sxml->VpcSecurityGroup as $v) {
                $item = new VpcSecurityGroupMembershipData(
                    ($this->exist($v->VpcSecurityGroupId) ? (string)$v->VpcSecurityGroupId : null),
                    ($this->exist($v->Status) ? (string)$v->Status : null)
                );
                $item->setRds($this->rds);
                $list->append($item);
                unset($item);
            }
        }
        return $list;
    }

    /**
     * CreateDBInstance action
     *
     * Creates a new DB instance.
     *
     * @param   CreateDBInstanceRequestData $request Created DB Instance request object
     * @return  DBInstanceData  Returns created DBInstance
     * @throws  ClientException
     * @throws  RdsException
     */
    public function createDBInstance(CreateDBInstanceRequestData $request)
    {
        $result = null;
        $options = $request->getQueryArray();
        if ($this->rds->getApiClientType() === Aws::CLIENT_SOAP) {
            if (isset($options['DBSecurityGroups.member.1']) || isset($options['VpcSecurityGroupIds.member.1'])) {
                foreach ($options as $k => $v) {
                    if (strpos($k, 'DBSecurityGroups.member.') !== false) {
                        $options['DBSecurityGroups']['DBSecurityGroupName'][] = $v;
                        unset($options[$k]);
                    } elseif (strpos($k, 'VpcSecurityGroupIds.member.') !== false) {
                        $options['VpcSecurityGroupIds']['VpcSecurityGroupId'][] = $v;
                        unset($options[$k]);
                    }
                }
            }
        }
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if (!$this->exist($sxml->CreateDBInstanceResult)) {
                throw new RdsException(sprintf(self::UNEXPECTED, 'create DBIntance'));
            }
            $result = $this->_loadDBInstanceData($sxml->CreateDBInstanceResult->DBInstance);
        }
        return $result;
    }

    /**
     * DeleteDBInstance action
     *
     * The DeleteDBInstance API deletes a previously provisioned RDS instance.
     * A successful response from the web service indicates the request
     * was received correctly. If a final DBSnapshot is requested the status
     * of the RDS instance will be "deleting" until the DBSnapshot is created.
     * DescribeDBInstance is used to monitor the status of this operation.
     * This cannot be canceled or reverted once submitted
     *
     * @param   string       $dBInstanceIdentifier      The DB Instance identifier for the DB Instance to be deleted.
     * @param   bool         $skipFinalSnapshot         optional Determines whether a final DB Snapshot is created
     *                                                  before the DB Instance is deleted
     * @param   string       $finalDBSnapshotIdentifier optional The DBSnapshotIdentifier of the new DBSnapshot
     *                                                  created when SkipFinalSnapshot is set to false
     * @return  DBInstanceData  Returns created DBInstance
     * @throws  ClientException
     * @throws  RdsException
     */
    public function deleteDBInstance($dBInstanceIdentifier, $skipFinalSnapshot = null, $finalDBSnapshotIdentifier = null)
    {
        $result = null;
        $options = array(
            'DBInstanceIdentifier' => (string) $dBInstanceIdentifier,
        );
        if ($skipFinalSnapshot !== null) {
            $options['SkipFinalSnapshot'] = $skipFinalSnapshot ? 'true' : 'false';
        }
        if ($finalDBSnapshotIdentifier !== null) {
            $options['FinalDBSnapshotIdentifier'] = (string) $finalDBSnapshotIdentifier;
            if (isset($options['SkipFinalSnapshot']) && $options['SkipFinalSnapshot'] === 'true') {
                throw new \InvalidArgumentException(sprintf(
                    'Specifiying FinalDBSnapshotIdentifier and also setting the '
                  . 'SkipFinalSnapshot parameter to true is forbidden.'
                ));
            }
        }
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if (!$this->exist($sxml->DeleteDBInstanceResult)) {
                throw new RdsException(sprintf(self::UNEXPECTED, 'delete DBIntance'));
            }
            $result = $this->_loadDBInstanceData($sxml->DeleteDBInstanceResult->DBInstance);
        }
        return $result;
    }

    /**
     * ModifyDBInstance action
     *
     * Modify settings for a DB Instance.
     * You can change one or more database configuration parameters by
     * specifying these parameters and the new values in the request.
     *
     * @param   ModifyDBInstanceRequestData $request Modify DB Instance request object
     * @return  DBInstanceData  Returns modified DBInstance
     * @throws  ClientException
     * @throws  RdsException
     */
    public function modifyDBInstance(ModifyDBInstanceRequestData $request)
    {
        $result = null;
        $options = $request->getQueryArray();
        if ($this->rds->getApiClientType() === Aws::CLIENT_SOAP) {
            if (isset($options['DBSecurityGroups.member.1']) || isset($options['VpcSecurityGroupIds.member.1'])) {
                foreach ($options as $k => $v) {
                    if (strpos($k, 'DBSecurityGroups.member.') !== false) {
                        $options['DBSecurityGroups']['DBSecurityGroupName'][] = $v;
                        unset($options[$k]);
                    } elseif (strpos($k, 'VpcSecurityGroupIds.member.') !== false) {
                        $options['VpcSecurityGroupIds']['VpcSecurityGroupId'][] = $v;
                        unset($options[$k]);
                    }
                }
            }
        }
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if (!$this->exist($sxml->ModifyDBInstanceResult)) {
                throw new RdsException(sprintf(self::UNEXPECTED, 'modify DBIntance'));
            }
            $result = $this->_loadDBInstanceData($sxml->ModifyDBInstanceResult->DBInstance);
        }
        return $result;
    }

    /**
     * RebootDBInstance action
     *
     * Reboots a previously provisioned RDS instance. This API results in the application of modified
     * DBParameterGroup parameters with ApplyStatus of pending-reboot to the RDS instance. This action is
     * taken as soon as possible, and results in a momentary outage to the RDS instance during which the RDS
     * instance status is set to rebooting. If the RDS instance is configured for MultiAZ, it is possible that the
     * reboot will be conducted through a failover. A DBInstance event is created when the reboot is completed.
     *
     * @param   string     $dBInstanceIdentifier The DB Instance identifier.
     *                                           This parameter is stored as a lowercase string
     * @param   bool       $forceFailover        optional When true, the reboot will be conducted through
     *                                           a MultiAZ failover. You cannot specify true if the instance
     *                                           is not configured for MultiAZ.
     * @return  DBInstanceData  Returns DBInstance
     * @throws  ClientException
     * @throws  RdsException
     */
    public function rebootDBInstance($dBInstanceIdentifier, $forceFailover = null)
    {
        $options = array(
            'DBInstanceIdentifier' => (string) $dBInstanceIdentifier,
        );
        if ($forceFailover !== null) {
            $options['ForceFailover'] = $forceFailover ? 'true' : 'false';
        }
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if (!$this->exist($sxml->RebootDBInstanceResult)) {
                throw new RdsException(sprintf(self::UNEXPECTED, 'reboot DBIntance'));
            }
            $result = $this->_loadDBInstanceData($sxml->RebootDBInstanceResult->DBInstance);
        }
        return $result;
    }

    /**
     * DescribeDBSecurityGroups action
     *
     * Returns a list of DBSecurityGroup descriptions.
     * If a DBSecurityGroupName is specified, the list will contain
     * only the descriptions of the specified DBSecurityGroup.
     *
     * @param   string     $dBSecurityGroupName optional The name of the DB Security Group to return details for.
     * @param   string     $marker              optional Pagination token, provided by a previous request.
     * @param   string     $maxRecords          optional The maximum number of records to include in the response.
     * @return  DBSecurityGroupList             Returns the list of the DBSecurityGroupData
     * @throws  ClientException
     * @throws  RdsException
     */
    public function describeDBSecurityGroups($dBSecurityGroupName = null, $marker = null, $maxRecords = null)
    {
        $result = null;
        $options = array();
        if ($dBSecurityGroupName !== null) {
            $options['DBSecurityGroupName'] = (string) $dBSecurityGroupName;
        }
        if ($marker !== null) {
            $options['Marker'] = (string) $marker;
        }
        if ($maxRecords !== null) {
            $options['MaxRecords'] = (int) $maxRecords;
        }
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            $result = new DBSecurityGroupList();
            $result->setRds($this->rds);
            $result->marker = $this->exist($sxml->DescribeDBSecurityGroupsResult->Marker) ?
                (string) $sxml->DescribeDBSecurityGroupsResult->Marker : null;
            if (isset($sxml->DescribeDBSecurityGroupsResult->DBSecurityGroups->DBSecurityGroup)) {
                foreach ($sxml->DescribeDBSecurityGroupsResult->DBSecurityGroups->DBSecurityGroup as $v) {
                    $item = $this->_loadDBSecurityGroupData($v);
                    $result->append($item);
                    unset($item);
                }
            }
        }
        return $result;
    }

    /**
     * Loads DBSecurityGroupData from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  DBSecurityGroupData Returns DBSecurityGroupData
     */
    protected function _loadDBSecurityGroupData(\SimpleXMLElement $sxml)
    {
        $item = null;
        if ($this->exist($sxml)) {
            $dbSecurityGroupName = (string) $sxml->DBSecurityGroupName;
            $item = $this->rds->getEntityManagerEnabled() ? $this->rds->dbSecurityGroup->get($dbSecurityGroupName) : null;
            if ($item === null) {
                $item = new DBSecurityGroupData();
                $item->setRds($this->rds);
                $bAttach = true;
            } else {
                $item->resetObject();
                $bAttach = false;
            }

            $item->dBSecurityGroupName = $dbSecurityGroupName;
            $item->dBSecurityGroupDescription = $this->exist($sxml->DBSecurityGroupDescription) ?
                (string) $sxml->DBSecurityGroupDescription : null;
            $item->ownerId = $this->exist($sxml->OwnerId) ? (string) $sxml->OwnerId : null;
            $item->vpcId = $this->exist($sxml->VpcId) ? (string) $sxml->VpcId : null;
            $item->eC2SecurityGroups = $this->_loadEC2SecurityGroupList($sxml->EC2SecurityGroups);
            $item->iPRanges = $this->_loadIPRangeList($sxml->IPRanges);

            if ($bAttach && $this->rds->getEntityManagerEnabled()) {
                $this->getEntityManager()->attach($item);
            }
        }
        return $item;
    }

    /**
     * Loads IPRangeList from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  IPRangeList Returns IPRangeList
     */
    protected function _loadIPRangeList(\SimpleXMLElement $sxml)
    {
        $list = new IPRangeList();
        $list->setRds($this->rds);
        if (!empty($sxml->IPRange)) {
            foreach ($sxml->IPRange as $v) {
                $item = new IPRangeData();
                $item->setRds($this->rds);
                $item->cIDRIP = $this->exist($v->CIDRIP) ? (string)$v->CIDRIP : null;
                $item->status = $this->exist($v->Status) ? (string)$v->Status : null;
                $list->append($item);
                unset($item);
            }
        }
        return $list;
    }

    /**
     * Loads EC2SecurityGroupList from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  EC2SecurityGroupList Returns EC2SecurityGroupList
     */
    protected function _loadEC2SecurityGroupList(\SimpleXMLElement $sxml)
    {
        $list = new EC2SecurityGroupList();
        $list->setRds($this->rds);
        if (!empty($sxml->EC2SecurityGroup)) {
            foreach ($sxml->EC2SecurityGroup as $v) {
                $item = new EC2SecurityGroupData();
                $item->setRds($this->rds);
                $item->eC2SecurityGroupId = $this->exist($v->EC2SecurityGroupId) ?
                    (string)$v->EC2SecurityGroupId : null;
                $item->eC2SecurityGroupName = $this->exist($v->EC2SecurityGroupName) ?
                    (string)$v->EC2SecurityGroupName : null;
                $item->eC2SecurityGroupOwnerId = $this->exist($v->EC2SecurityGroupOwnerId) ?
                    (string)$v->EC2SecurityGroupOwnerId : null;
                $item->status = $this->exist($v->Status) ? (string)$v->Status : null;
                $list->append($item);
                unset($item);
            }
        }
        return $list;
    }

    /**
     * DeleteDBSecurityGroup action
     *
     * Deletes a DB Security Group.
     * Note! The specified DB Security Group must not be associated with any DB Instances.
     *
     * @param   string     $dBSecurityGroupName The Name of the DB security group to delete.
     * @return  bool       Returns true on success or throws an exception.
     * @throws  ClientException
     * @throws  RdsException
     */
    public function deleteDBSecurityGroup($dBSecurityGroupName)
    {
        $result = false;
        $options = array(
            'DBSecurityGroupName' => (string) $dBSecurityGroupName
        );
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            $result = true;
            if ($this->rds->getEntityManagerEnabled() &&
                null !== ($item = $this->rds->dbSecurityGroup->get($options['DBSecurityGroupName']))) {
                $this->getEntityManager()->detach($item);
            }
        }
        return $result;
    }

    /**
     * CreateDBSecurityGroup
     *
     * Creates a new DB Security Group. DB Security Groups control access to a DB Instance
     *
     * @param   string     $name        The name for the DB Security Group. This value is stored as a lowercase string
     * @param   string     $description The description for the DB Security Group
     * @return  DBSecurityGroupData     Returns DBSecurityGroupData on success or throws an exception.
     * @throws  ClientException
     * @throws  RdsException
     */
    public function createDBSecurityGroup($name, $description)
    {
        $result = null;
        $options = array(
            'DBSecurityGroupDescription' => (string) $description,
            'DBSecurityGroupName'        => (string) $name,
        );
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if (!$this->exist($sxml->CreateDBSecurityGroupResult)) {
                throw new RdsException(sprintf(self::UNEXPECTED, 'create DBSecurityGroup'));
            }
            $result = $this->_loadDBSecurityGroupData($sxml->CreateDBSecurityGroupResult->DBSecurityGroup);
        }
        return $result;
    }

    /**
     * AuthorizeDBSecurityGroupIngress action
     *
     * Enables ingress to a DBSecurityGroup using one of two forms of authorization.
     * First, EC2 or VPC Security Groups can be added to the DBSecurityGroup
     * if the application using the database is running on EC2 or VPC instances.
     * Second, IP ranges are available if the application accessing your database is running on
     * the Internet. Required parameters for this API are one of CIDR range,
     * EC2SecurityGroupId for VPC, or (EC2SecurityGroupOwnerId and either EC2SecurityGroupName or
     * EC2SecurityGroupId for non-VPC).
     *
     * Note! You cannot authorize ingress from an EC2 security group in one Region to an Amazon RDS DB
     * Instance in another.You cannot authorize ingress from a VPC security group in one VPC to an
     * Amazon RDS DB Instance in another.
     *
     * @param   DBSecurityGroupIngressRequestData $request
     * @return  DBSecurityGroupData     Returns DBSecurityGroupData on success or throws an exception.
     * @throws  ClientException
     * @throws  RdsException
     */
    public function authorizeDBSecurityGroupIngress(DBSecurityGroupIngressRequestData $request)
    {
        $result = null;
        $options = $request->getQueryArray();
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if (!$this->exist($sxml->AuthorizeDBSecurityGroupIngressResult)) {
                throw new RdsException(sprintf(self::UNEXPECTED, 'authorize DBSecurityGroupIngress'));
            }
            $result = $this->_loadDBSecurityGroupData($sxml->AuthorizeDBSecurityGroupIngressResult->DBSecurityGroup);
        }
        return $result;
    }

    /**
     * RevokeDBSecurityGroupIngress action
     *
     * Revokes ingress from a DBSecurityGroup for previously
     * authorized IP ranges or EC2 or VPC Security Groups.
     * Required parameters for this API are one of CIDRIP,
     * EC2SecurityGroupId for VPC, or (EC2SecurityGroupOwnerId and
     * either EC2SecurityGroupName or EC2SecurityGroupId).
     *
     * @param   DBSecurityGroupIngressRequestData $request
     * @return  DBSecurityGroupData     Returns DBSecurityGroupData on success or throws an exception.
     * @throws  ClientException
     * @throws  RdsException
     */
    public function revokeDBSecurityGroupIngress(DBSecurityGroupIngressRequestData $request)
    {
        $result = null;
        $options = $request->getQueryArray();
        $response = $this->client->call(ucfirst(__FUNCTION__), $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if (!$this->exist($sxml->RevokeDBSecurityGroupIngressResult)) {
                throw new RdsException(sprintf(self::UNEXPECTED, 'revoke DBSecurityGroupIngress'));
            }
            $result = $this->_loadDBSecurityGroupData($sxml->RevokeDBSecurityGroupIngressResult->DBSecurityGroup);
        }
        return $result;
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
    public function describeDBParameterGroups($dBParameterGroupName = null, $marker = null, $maxRecords = null)
    {
        $result = null;
        $options = array();
        $action = ucfirst(__FUNCTION__);
        if ($dBParameterGroupName !== null) {
            $options['DBParameterGroupName'] = (string) $dBParameterGroupName;
        }
        if ($marker !== null) {
            $options['Marker'] = (string) $marker;
        }
        if ($maxRecords !== null) {
            $options['MaxRecords'] = (int) $maxRecords;
        }
        $response = $this->client->call($action, $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if (!$this->exist($sxml->{$action . 'Result'})) {
                throw new RdsException(sprintf(self::UNEXPECTED, $action));
            }
            $ptr = $sxml->{$action . 'Result'};
            $result = new DBParameterGroupList();
            $result->setRds($this->rds);
            $result->marker = $this->exist($ptr->Marker) ? (string) $ptr->Marker : null;
            if (isset($ptr->DBParameterGroups->DBParameterGroup)) {
                foreach ($ptr->DBParameterGroups->DBParameterGroup as $v) {
                    $item = $this->_loadDBParameterGroupData($v);
                    $result->append($item);
                    unset($item);
                }
            }
        }
        return $result;
    }

    /**
     * Loads DBParameterGroupData from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  DBParameterGroupData Returns DBParameterGroupData
     */
    protected function _loadDBParameterGroupData(\SimpleXMLElement $sxml)
    {
        $item = null;
        if ($this->exist($sxml)) {
            $dbParameterGroupName = (string) $sxml->DBParameterGroupName;
            $item = $this->rds->getEntityManagerEnabled() ? $this->rds->dbParameterGroup->get($dbParameterGroupName) : null;
            if ($item === null) {
                $item = new DBParameterGroupData(
                    $dbParameterGroupName,
                    ($this->exist($sxml->DBParameterGroupFamily) ? (string) $sxml->DBParameterGroupFamily : null),
                    ($this->exist($sxml->Description) ? (string) $sxml->Description : null)
                );
                $item->setRds($this->rds);
                $bAttach = true;
            } else {
                $item->resetObject();
                $item->dBParameterGroupName = $dbParameterGroupName;
                $item->dBParameterGroupFamily = $this->exist($sxml->DBParameterGroupFamily) ?
                    (string) $sxml->DBParameterGroupFamily : null;
                $item->description = $this->exist($sxml->Description) ? (string) $sxml->Description : null;
                $bAttach = false;
            }

            if ($bAttach && $this->rds->getEntityManagerEnabled()) {
                $this->getEntityManager()->attach($item);
            }
        }
        return $item;
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
    public function createDBParameterGroup(DBParameterGroupData $request)
    {
        $result = null;
        $options = $request->getQueryArray();
        $action = ucfirst(__FUNCTION__);
        $response = $this->client->call($action, $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if (!$this->exist($sxml->{$action . 'Result'})) {
                throw new RdsException(sprintf(self::UNEXPECTED, $action));
            }
            $ptr = $sxml->{$action . 'Result'};
            $result = $this->_loadDBParameterGroupData($ptr->DBParameterGroup);
        }
        return $result;
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
    public function deleteDBParameterGroup($dBParameterGroupName)
    {
        $result = false;
        $options = array(
            'DBParameterGroupName' => (string) $dBParameterGroupName,
        );
        $action = ucfirst(__FUNCTION__);
        $response = $this->client->call($action, $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            $result = true;
            if ($this->rds->getEntityManagerEnabled() &&
                null !== ($item = $this->rds->dbParameterGroup->get($options['DBParameterGroupName']))) {
                $this->getEntityManager()->detach($item);
            }
        }
        return $result;
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
     * @param   string        $dBParameterGroupName The name of DB Parameter Group to modify.
     * @param   ParameterList $parameters           An list of parameter names, values, and the apply method
     *                                              for the parameter update. At least one parameter name, value,
     *                                              and apply method must be supplied;
     *                                              subsequent arguments are optional.
     *                                              A maximum of 20 parameters may be modified in a single request.
     *                                              Valid Values (for the application method): immediate | pending-reboot
     * @return  string        Returns DBParameterGroupName on success or throws an exception.
     * @throws  ClientException
     * @throws  RdsException
     */
    public function modifyDBParameterGroup($dBParameterGroupName, ParameterList $parameters)
    {
        $result = false;
        $options = array(
            'DBParameterGroupName' => (string) $dBParameterGroupName,
        );
        if ($this->rds->getApiClientType() == Aws::CLIENT_SOAP) {
            $parameter = array();
            foreach ($parameters as $v) {
                $parameter[] = $v->getQueryArray();
            }
            $options['Parameters']['Parameter'] = $parameter;
        } else {
            $options = array_merge($options, array_filter($parameters->getQueryArray('Parameters'), function($v){
                return $v !== null;
            }));
        }

        $action = ucfirst(__FUNCTION__);
        $response = $this->client->call($action, $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if (!$this->exist($sxml->{$action . 'Result'})) {
                throw new RdsException(sprintf(self::UNEXPECTED, $action));
            }
            $ptr = $sxml->{$action . 'Result'};
            $result = (string) $ptr->DBParameterGroupName;
        }
        return $result;
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
     * @param   string        $dBParameterGroupName The name of DB Parameter Group to modify.
     * @param   ParameterList $parameters           optional An list of parameter names, values, and the apply method
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
    public function resetDBParameterGroup($dBParameterGroupName, ParameterList $parameters = null, $resetAllParameters = null)
    {
        $result = false;
        $options = array(
            'DBParameterGroupName' => (string) $dBParameterGroupName
        );
        if ($parameters !== null) {
            if ($this->rds->getApiClientType() == Aws::CLIENT_SOAP) {
                $parameter = array();
                foreach ($parameters as $v) {
                    $parameter[] = $v->getQueryArray();
                }
                $options['Parameters']['Parameter'] = $parameter;
            } else {
                $options = array_merge($options, array_filter($parameters->getQueryArray('Parameters'), function($v){
                    return $v !== null;
                }));
            }
        } elseif ($resetAllParameters !== null) {
            $options['ResetAllParameters'] = ($resetAllParameters ? 'true' : 'false');
        }
        $action = ucfirst(__FUNCTION__);
        $response = $this->client->call($action, $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if (!$this->exist($sxml->{$action . 'Result'})) {
                throw new RdsException(sprintf(self::UNEXPECTED, $action));
            }
            $ptr = $sxml->{$action . 'Result'};
            $result = (string) $ptr->DBParameterGroupName;
        }
        return $result;
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
    public function describeDBParameters($dBParameterGroupName, $source = null, $marker = null, $maxRecords = null)
    {
        $result = null;
        $options = array(
            'DBParameterGroupName' => (string) $dBParameterGroupName,
        );
        $action = ucfirst(__FUNCTION__);
        if ($source !== null) {
            $options['Source'] = (string) $source;
        }
        if ($marker !== null) {
            $options['Marker'] = (string) $marker;
        }
        if ($maxRecords !== null) {
            $options['MaxRecords'] = (int) $maxRecords;
        }
        $response = $this->client->call($action, $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if (!$this->exist($sxml->{$action . 'Result'})) {
                throw new RdsException(sprintf(self::UNEXPECTED, $action));
            }
            $ptr = $sxml->{$action . 'Result'};
            $result = new ParameterList();
            $result->setRds($this->rds);
            $result->marker = $this->exist($ptr->Marker) ? (string) $ptr->Marker : null;
            if (isset($ptr->Parameters->Parameter)) {
                foreach ($ptr->Parameters->Parameter as $v) {
                    $item = $this->_loadParameterData($v);
                    $result->append($item);
                    unset($item);
                }
            }
        }
        return $result;
    }

    /**
     * Loads ParameterData from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  ParameterData Returns ParameterData
     */
    protected function _loadParameterData(\SimpleXMLElement $sxml)
    {
        $item = null;
        if ($this->exist($sxml)) {
            $item = new ParameterData(
                (string) $sxml->ParameterName,
                ($this->exist($sxml->ApplyMethod) ? (string) $sxml->ParameterName : null),
                ($this->exist($sxml->ParameterValue) ? (string) $sxml->ParameterValue : null)
            );
            $item->allowedValues = $this->exist($sxml->AllowedValues) ? (string) $sxml->AllowedValues : null;
            $item->applyType = $this->exist($sxml->ApplyType) ? (string) $sxml->ApplyType : null;
            $item->dataType = $this->exist($sxml->DataType) ? (string) $sxml->DataType : null;
            $item->description = $this->exist($sxml->Description) ? (string) $sxml->Description : null;
            $item->isModifiable = $this->exist($sxml->IsModifiable) ? (((string)$sxml->IsModifiable) == 'true') : null;
            $item->minimumEngineVersion = $this->exist($sxml->MinimumEngineVersion) ?
                (string) $sxml->MinimumEngineVersion : null;
            $item->source = $this->exist($sxml->Source) ? (string) $sxml->Source : null;
        }
        return $item;
    }

    /**
     * DescribeDBSnapshots action
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
     * @return  DBSnapshotList Returns DBSnapshotList on success or throws an exception.
     * @throws  ClientException
     * @throws  RdsException
     */
    public function describeDBSnapshots($dBInstanceIdentifier = null, $dBSnapshotIdentifier = null, $snapshotType = null,
                                        $marker = null, $maxRecords = null)
    {
        $result = null;
        $options = array();
        $action = ucfirst(__FUNCTION__);
        if ($dBInstanceIdentifier !== null) {
            $options['DBInstanceIdentifier'] = (string) $dBInstanceIdentifier;
        }
        if ($dBSnapshotIdentifier !== null) {
            $options['DBSnapshotIdentifier'] = (string) $dBSnapshotIdentifier;
        }
        if ($snapshotType !== null) {
            $options['SnapshotType'] = (string) $snapshotType;
        }
        if ($marker !== null) {
            $options['Marker'] = (string) $marker;
        }
        if ($maxRecords !== null) {
            $options['MaxRecords'] = (int) $maxRecords;
        }
        $response = $this->client->call($action, $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if (!$this->exist($sxml->{$action . 'Result'})) {
                throw new RdsException(sprintf(self::UNEXPECTED, $action));
            }
            $ptr = $sxml->{$action . 'Result'};
            $result = new DBSnapshotList();
            $result->setRds($this->rds);
            $result->marker = $this->exist($ptr->Marker) ? (string) $ptr->Marker : null;
            if (isset($ptr->DBSnapshots->DBSnapshot)) {
                foreach ($ptr->DBSnapshots->DBSnapshot as $v) {
                    $item = $this->_loadDBSnapshotData($v);
                    $result->append($item);
                    unset($item);
                }
            }
        }
        return $result;
    }

    /**
     * Loads DBSnapshotData from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  DBSnapshotData Returns DBSnapshotData
     */
    protected function _loadDBSnapshotData(\SimpleXMLElement $sxml)
    {
        $item = null;
        if ($this->exist($sxml)) {
            $dBSnapshotIdentifier = (string) $sxml->DBSnapshotIdentifier;
            $item = $this->rds->getEntityManagerEnabled() ? $this->rds->dbSnapshot->get($dBSnapshotIdentifier) : null;
            if ($item === null) {
                $item = new DBSnapshotData();
                $item->setRds($this->rds);
                $bAttach = true;
            } else {
                $item->resetObject();
                $bAttach = false;
            }

            $item->dBSnapshotIdentifier = $dBSnapshotIdentifier;
            $item->allocatedStorage = $this->exist($sxml->AllocatedStorage) ? (int) $sxml->AllocatedStorage : null;
            $item->availabilityZone = $this->exist($sxml->AvailabilityZone) ? (string) $sxml->AvailabilityZone : null;
            $item->dBInstanceIdentifier = $this->exist($sxml->DBInstanceIdentifier) ?
                (string) $sxml->DBInstanceIdentifier : null;
            $item->engine = $this->exist($sxml->Engine) ? (string) $sxml->Engine : null;
            $item->engineVersion = $this->exist($sxml->EngineVersion) ? (string) $sxml->EngineVersion : null;
            $item->instanceCreateTime = $this->exist($sxml->InstanceCreateTime) ?
                new DateTime((string)$sxml->InstanceCreateTime, new DateTimeZone('UTC')) : null;
            $item->snapshotCreateTime = $this->exist($sxml->SnapshotCreateTime) ?
                new DateTime((string)$sxml->SnapshotCreateTime, new DateTimeZone('UTC')) : null;
            $item->iops = $this->exist($sxml->Iops) ? (int) $sxml->Iops : null;
            $item->port = $this->exist($sxml->Port) ? (int) $sxml->Port : null;
            $item->licenseModel = $this->exist($sxml->LicenseModel) ? (string) $sxml->LicenseModel : null;
            $item->masterUsername = $this->exist($sxml->MasterUsername) ? (string) $sxml->MasterUsername : null;
            $item->snapshotType = $this->exist($sxml->SnapshotType) ? (string) $sxml->SnapshotType : null;
            $item->status = $this->exist($sxml->Status) ? (string) $sxml->Status : null;
            $item->vpcId = $this->exist($sxml->VpcId) ? (string) $sxml->VpcId : null;

            if ($bAttach && $this->rds->getEntityManagerEnabled()) {
                $this->getEntityManager()->attach($item);
            }
        }
        return $item;
    }

    /**
     * CreateDBSnapshot
     *
     * Creates a DBSnapshot. The source DBInstance must be in "available" state.
     *
     * @param   string     $dBInstanceIdentifier The DB Instance Identifier
     * @param   string     $dBSnapshotIdentifier The identifier for the DB Snapshot.
     * @return  DBSnapshotData Returns DBSnapshotData on success or throws an exception.
     * @throws  ClientException
     * @throws  RdsException
     */
    public function createDBSnapshot($dBInstanceIdentifier, $dBSnapshotIdentifier)
    {
        $result = null;
        $options = array(
            'DBInstanceIdentifier' => (string) $dBInstanceIdentifier,
            'DBSnapshotIdentifier' => (string) $dBSnapshotIdentifier,
        );
        $action = ucfirst(__FUNCTION__);
        $response = $this->client->call($action, $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if (!$this->exist($sxml->{$action . 'Result'})) {
                throw new RdsException(sprintf(self::UNEXPECTED, $action));
            }
            $ptr = $sxml->{$action . 'Result'};
            $result = $this->_loadDBSnapshotData($ptr->DBSnapshot);
        }
        return $result;
    }

    /**
     * DeleteDBSnapshot action
     *
     * Deletes a DBSnapshot.
     * Note! The DBSnapshot must be in the available state to be deleted
     *
     * @param   string     $dBSnapshotIdentifier The Identifier for the DB Snapshot to delete.
     * @return  DBSnapshotData Returns DBSnapshotData on success or throws an exception.
     * @throws  ClientException
     * @throws  RdsException
     */
    public function deleteDBSnapshot($dBSnapshotIdentifier)
    {
        $result = null;
        $options = array(
            'DBSnapshotIdentifier' => (string) $dBSnapshotIdentifier,
        );
        $action = ucfirst(__FUNCTION__);
        $response = $this->client->call($action, $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if (!$this->exist($sxml->{$action . 'Result'})) {
                throw new RdsException(sprintf(self::UNEXPECTED, $action));
            }
            $ptr = $sxml->{$action . 'Result'};
            $result = $this->_loadDBSnapshotData($ptr->DBSnapshot);
        }
        return $result;
    }

    /**
     * RestoreDBInstanceFromDBSnapshot action
     *
     * Creates a new DB Instance from a DB snapshot.The target database is created from the source database
     * restore point with the same configuration as the original source database, except that the new RDS
     * instance is created with the default security group.
     *
     * @param   RestoreDBInstanceFromDBSnapshotRequestData $request The request object.
     * @return  DBInstanceData Returns DBInstanceData on success or throws an exception.
     * @throws  ClientException
     * @throws  RdsException
     */
    public function restoreDBInstanceFromDBSnapshot(RestoreDBInstanceFromDBSnapshotRequestData $request)
    {
        $result = null;
        $options = $request->getQueryArray();
        $action = ucfirst(__FUNCTION__);
        $response = $this->client->call($action, $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if (!$this->exist($sxml->{$action . 'Result'})) {
                throw new RdsException(sprintf(self::UNEXPECTED, $action));
            }
            $ptr = $sxml->{$action . 'Result'};
            $result = $this->_loadDBInstanceData($ptr->DBInstance);
        }
        return $result;
    }

    /**
     * DescribeEvents action
     *
     * Returns events related to DB instances, DB security groups, DB Snapshots, and DB parameter groups
     * for the past 14 days. Events specific to a particular DB Iinstance, DB security group, DB Snapshot, or
     * DB parameter group can be obtained by providing the source identifier as a parameter. By default, the
     * past hour of events are returned.
     *
     * @param   DescribeEventRequestData $request optional Request object.
     * @return  EventList Returns EventList on success or throws an exception.
     * @throws  ClientException
     * @throws  RdsException
     */
    public function describeEvents(DescribeEventRequestData $request = null)
    {
        $result = null;
        if ($request !== null) {
            $options = $request->getQueryArray();
            if ($this->rds->getApiClientType() == Aws::CLIENT_SOAP) {
                if (isset($options['EventCategories.member.1'])) {
                    foreach ($options as $k => $v) {
                        if (strpos($k, 'EventCategories.member.') === 0) {
                            $options['EventCategories']['EventCategory'][] = $v;
                            unset($options[$k]);
                        }
                    }
                }
            }
        } else {
            $options = array();
        }
        $action = ucfirst(__FUNCTION__);
        $response = $this->client->call($action, $options);
        if ($response->getError() === false) {
            $sxml = simplexml_load_string($response->getRawContent());
            if (!$this->exist($sxml->{$action . 'Result'})) {
                throw new RdsException(sprintf(self::UNEXPECTED, $action));
            }
            $ptr = $sxml->{$action . 'Result'};
            $result = new EventList();
            $result->setRds($this->rds);
            $result->marker = $this->exist($ptr->Marker) ? (string) $ptr->Marker : null;
            if (isset($ptr->Events->Event)) {
                foreach ($ptr->Events->Event as $v) {
                    $item = $this->_loadEventData($v);
                    $result->append($item);
                    unset($item);
                }
            }
        }
        return $result;
    }

    /**
     * Loads EventData from simple xml object
     *
     * @param   \SimpleXMLElement $sxml
     * @return  EventData Returns EventData
     */
    protected function _loadEventData(\SimpleXMLElement $sxml)
    {
        $item = null;
        if ($this->exist($sxml)) {
            $item = new EventData();
            $item->setRds($this->rds);
            $item->date = new DateTime((string)$sxml->Date, new DateTimeZone('UTC'));
            $item->message = (string)$sxml->Message;
            $item->sourceIdentifier = $this->exist($sxml->SourceIdentifier) ? (string) $sxml->SourceIdentifier : null;
            $item->sourceType = $this->exist($sxml->SourceType) ? (string) $sxml->SourceType : null;
            if (!empty($sxml->EventCategories->EventCategory)) {
                $item->eventCategories = array();
                foreach ($sxml->EventCategories->EventCategory as $v) {
                    $item->eventCategories[] = (string) $v;
                }
            }
        }
        return $item;
    }
}