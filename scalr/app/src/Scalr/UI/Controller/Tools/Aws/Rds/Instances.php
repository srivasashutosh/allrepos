<?php

class Scalr_UI_Controller_Tools_Aws_Rds_Instances extends Scalr_UI_Controller
{
    const CALL_PARAM_NAME = 'instanceId';

    public static function getPermissionDefinitions()
    {
        return array();
    }

    public function defaultAction()
    {
        $this->viewAction();
    }

    public function viewAction()
    {
        $this->response->page('ui/tools/aws/rds/instances/view.js', array(
            'locations' => self::loadController('Platforms')->getCloudLocations(SERVER_PLATFORMS::EC2, false)
        ));
    }

    public function createAction()
    {
        $this->response->page('ui/tools/aws/rds/instances/create.js', array(
            'locations' => self::loadController('Platforms')->getCloudLocations(SERVER_PLATFORMS::EC2, false)
        ));
    }

    public function editAction()
    {
        $dbinstance = $this->getEnvironment()->aws($this->getParam('cloudLocation'))->rds->dbInstance->describe($this->getParam(self::CALL_PARAM_NAME))->get(0)->toArray();
        $this->response->page('ui/tools/aws/rds/instances/create.js', array(
            'locations' => self::loadController('Platforms')->getCloudLocations(SERVER_PLATFORMS::EC2, false),
            'instance' => $dbinstance
        ));
    }

    public function xModifyInstanceAction()
    {
        $aws = $this->getEnvironment()->aws($this->getParam('cloudLocation'));

        $request = new \Scalr\Service\Aws\Rds\DataType\ModifyDBInstanceRequestData($this->getParam('DBInstanceIdentifier'));
        $request->dBParameterGroupName = $this->getParam('DBParameterGroup');
        $request->dBSecurityGroups = $this->getParam('DBSecurityGroups');
        $request->preferredMaintenanceWindow = $this->getParam('PreferredMaintenanceWindow');
        $request->masterUserPassword = $this->getParam('MasterUserPassword') != '' ? $this->getParam('MasterUserPassword') : null;
        $request->allocatedStorage = $this->getParam('AllocatedStorage');
        $request->dBInstanceClass = $this->getParam('DBInstanceClass');
        $request->backupRetentionPeriod = $this->getParam('BackupRetentionPeriod');
        $request->preferredBackupWindow = $this->getParam('PreferredBackupWindow');
        $request->multiAZ = $this->getParam('MultiAZ') ? true : false;

        $aws->rds->dbInstance->modify($request);
        $this->response->success("DB Instance successfully modified");
    }

    public function xLaunchInstanceAction()
    {
        $aws = $this->getEnvironment()->aws($this->getParam('cloudLocation'));

        $request = new \Scalr\Service\Aws\Rds\DataType\CreateDBInstanceRequestData(
            $this->getParam('DBInstanceIdentifier'),
            $this->getParam('AllocatedStorage'),
            $this->getParam('DBInstanceClass'),
            $this->getParam('Engine'),
            $this->getParam('MasterUsername'),
            $this->getParam('MasterUserPassword')
        );
        $request->port = $this->getParam('Port') ?: null;
        $request->dBName = $this->getParam('DBName') ?: null;
        $request->dBParameterGroupName = $this->getParam('DBParameterGroup') ?: null;
        $request->dBSecurityGroups = $this->getParam('DBSecurityGroups');
        $request->availabilityZone = $this->getParam('AvailabilityZone') ?: null;
        $request->backupRetentionPeriod = $this->getParam('BackupRetentionPeriod');
        $request->preferredBackupWindow = $this->getParam('PreferredBackupWindow');
        $request->preferredMaintenanceWindow = $this->getParam('PreferredMaintenanceWindow');
        $request->multiAZ = $this->getParam('MultiAZ') ? true : false;

        $aws->rds->dbInstance->create($request);

        $this->response->success("DB Instance successfully created");
    }


    public function detailsAction()
    {
        $aws = $this->getEnvironment()->aws($this->getParam('cloudLocation'));

        /* @var $dbinstance \Scalr\Service\Aws\Rds\DataType\DBInstanceData */
        $dbinstance = $aws->rds->dbInstance->describe($this->getParam(self::CALL_PARAM_NAME))->get(0);

        $sGroups = array();
        foreach ($dbinstance->dBSecurityGroups as $g)
            $sGroups[] = "{$g->dBSecurityGroupName} ({$g->status})";

        $pGroups = array();
        foreach ($dbinstance->dBParameterGroups as $g)
            $pGroups[] = "{$g->dBParameterGroupName} ({$g->parameterApplyStatus})";

        $form = array(
            array(
                'xtype' => 'fieldset',
                'labelWidth' => 200,
                'items' => array(
                    array(
                        'xtype' => 'displayfield',
                        'labelWidth' => 200,
                        'fieldLabel' => 'Name',
                        'value' => (string) $dbinstance->dBInstanceIdentifier
                    ),
                    array(
                        'xtype' => 'displayfield',
                        'labelWidth' => 200,
                        'fieldLabel' => 'Engine',
                        'value' => isset($dbinstance->pendingModifiedValues) && $dbinstance->pendingModifiedValues->engine ? $dbinstance->engine. ' <i><font color="red">New value (' . $dbinstance->pendingModifiedValues->engine . ') is pending</font></i>' : $dbinstance->engine,
                    ),
                    array(
                        'xtype' => 'displayfield',
                        'labelWidth' => 200,
                        'fieldLabel' => 'Engine Version',
                        'value' => isset($dbinstance->pendingModifiedValues) && $dbinstance->pendingModifiedValues->engineVersion ? $dbinstance->engineVersion. ' <i><font color="red">New value (' . $dbinstance->pendingModifiedValues->engineVersion . ') is pending</font></i>' : $dbinstance->engineVersion,
                    ),
                    array(
                        'xtype' => 'displayfield',
                        'labelWidth' => 200,
                        'fieldLabel' => 'DNS Name',
                        'value' => isset($dbinstance->endpoint) ? $dbinstance->endpoint->address : '',
                    ),
                    array(
                        'xtype' => 'displayfield',
                        'labelWidth' => 200,
                        'fieldLabel' => 'Port',
                        'value' => isset($dbinstance->pendingModifiedValues) && $dbinstance->pendingModifiedValues->port ?
                            (string) $dbinstance->endpoint->port . ' <i><font color="red">New value (' . $dbinstance->pendingModifiedValues->port . ') is pending</font></i>' : (string)$dbinstance->endpoint->port
                    ),
                    array(
                        'xtype' => 'displayfield',
                        'labelWidth' => 200,
                        'fieldLabel' => 'Created at',
                        'value' => Scalr_Util_DateTime::convertTz($dbinstance->instanceCreateTime)
                    ),
                    array(
                        'xtype' => 'displayfield',
                        'labelWidth' => 200,
                        'fieldLabel' => 'Status',
                        'value' => $dbinstance->dBInstanceStatus
                    )
                )
            ),
            array(
                'xtype' => 'fieldset',
                'labelWidth' => 200,
                'items' => array(
                    array(
                        'xtype' => 'displayfield',
                        'labelWidth' => 200,
                        'fieldLabel' => 'Availability Zone',
                        'value' => isset($dbinstance->availabilityZone) ? $dbinstance->availabilityZone : '',
                    ),
                    array(
                        'xtype' => 'displayfield',
                        'labelWidth' => 200,
                        'fieldLabel' => 'MultiAZ',
                        'value' => ($dbinstance->multiAZ ? 'Enabled' : 'Disabled') .
                            (isset($dbinstance->pendingModifiedValues) && isset($dbinstance->pendingModifiedValues->multiAZ) ?
                                ' <i><font color="red">New value(' . ($dbinstance->pendingModifiedValues->multiAZ ? 'true' : 'false') . ') is pending</font></i>' : '')
                    ),
                    array(
                        'xtype' => 'displayfield',
                        'labelWidth' => 200,
                        'fieldLabel' => 'Type',
                        'value' => isset($dbinstance->pendingModifiedValues) && $dbinstance->pendingModifiedValues->dBInstanceClass ?
                            $dbinstance->dBInstanceClass . ' <i><font color="red">New value ('. $dbinstance->pendingModifiedValues->dBInstanceClass.') is pending</font></i>' : $dbinstance->dBInstanceClass
                    ),
                    array(
                        'xtype' => 'displayfield',
                        'labelWidth' => 200,
                        'fieldLabel' => 'Allocated storage',
                        'value' => isset($dbinstance->pendingModifiedValues) && $dbinstance->pendingModifiedValues->allocatedStorage ? (string) $dbinstance->allocatedStorage . ' GB' . ' <i><font color="red">New value (' . $dbinstance->pendingModifiedValues->allocatedStorage . ') is pending</font></i>' : (string) $dbinstance->allocatedStorage
                    )
                )
            ),
            array(
                'xtype' => 'fieldset',
                'labelWidth' => 200,
                'items' => array(
                    'xtype' => 'displayfield',
                    'labelWidth' => 200,
                    'fieldLabel' => 'Security groups',
                    'value' => implode(', ', $sGroups)
                )
            ),
            array(
                'xtype' => 'fieldset',
                'labelWidth' => 200,
                'items' => array(
                    'xtype' => 'displayfield',
                    'labelWidth' => 200,
                    'fieldLabel' => 'Parameter groups',
                    'value' => implode(', ', $pGroups)
                )
            ),
            array(
                'xtype' => 'fieldset',
                'labelWidth' => 200,
                'items' => array(
                    array(
                        'xtype' => 'displayfield',
                        'labelWidth' => 200,
                        'fieldLabel' => 'Preferred maintenance window',
                        'value' => $dbinstance->preferredMaintenanceWindow
                    ),
                    array(
                        'xtype' => 'displayfield',
                        'labelWidth' => 200,
                        'fieldLabel' => 'Preferred backup window',
                        'value' => $dbinstance->preferredBackupWindow
                    ),
                    array(
                        'xtype' => 'displayfield',
                        'labelWidth' => 200,
                        'fieldLabel' => 'Backup retention period',
                        'value' => isset($dbinstance->pendingModifiedValues) && $dbinstance->pendingModifiedValues->backupRetentionPeriod ?
                            $dbinstance->backupRetentionPeriod. ' <i><font color="red">(Pending Modified)</font></i>' : $dbinstance->backupRetentionPeriod
                    )
                )
            )
        );

        $this->response->page('ui/tools/aws/rds/instances/details.js', $form);
    }

    public function xRebootAction()
    {
        $aws = $this->getEnvironment()->aws($this->getParam('cloudLocation'));
        $aws->rds->dbInstance->reboot($this->getParam('instanceId'));
        $this->response->success();
    }

    public function xTerminateAction()
    {
        $aws = $this->getEnvironment()->aws($this->getParam('cloudLocation'));
        $aws->rds->dbInstance->delete($this->getParam('instanceId'), true);
        $this->response->success();
    }

    public function xGetParametersAction()
    {
        $aws = $this->getEnvironment()->aws($this->getParam('cloudLocation'));
        $groups = $aws->rds->dbParameterGroup->describe();
        $sgroups = $aws->rds->dbSecurityGroup->describe();
        $azlist = $aws->ec2->availabilityZone->describe();
        $zones = array();
        /* @var $az \Scalr\Service\Aws\Ec2\DataType\AvailabilityZoneData */
        foreach ($azlist as $az) {
            if (stristr($az->zoneState, 'available')) {
                $zones[] = array(
                    'id'   => $az->zoneName,
                    'name' => $az->zoneName,
                );
            }
        }
        $this->response->data(array(
            'groups'  => $groups->toArray(),
            'sgroups' => $sgroups->toArray(),
            'zones'   => $zones,
        ));
    }

    public function xListInstancesAction()
    {
        $this->request->defineParams(array(
            'cloudLocation',
            'sort' => array('type' => 'json', 'default' => array('property' => 'id', 'direction' => 'ASC'))
        ));

        $aws = $this->getEnvironment()->aws($this->getParam('cloudLocation'));

        $rows = $aws->rds->dbInstance->describe();
        $rowz = array();

        /* @var $pv \Scalr\Service\Aws\Rds\DataType\DBInstanceData */
        foreach ($rows as $pv)
            $rowz[] = array(
                'engine'	=> (string)$pv->engine,
                'status'	=> (string)$pv->dBInstanceStatus,
                'hostname'	=> (isset($pv->endpoint) ? (string)$pv->endpoint->address : ''),
                'port'		=> (isset($pv->endpoint) ? (string)$pv->endpoint->port : ''),
                'name'		=> (string)$pv->dBInstanceIdentifier,
                'username'	=> (string)$pv->masterUsername,
                'type'		=> (string)$pv->dBInstanceClass,
                'storage'	=> (string)$pv->allocatedStorage,
                'dtadded'	=> $pv->instanceCreateTime,
                'avail_zone'=> (string)$pv->availabilityZone
            );

        $response = $this->buildResponseFromData($rowz);
        foreach ($response['data'] as &$row) {
            $row['dtadded'] = $row['dtadded'] ? Scalr_Util_DateTime::convertTz($row['dtadded']) : '';
        }
        $this->response->data($response);
    }

    public function restoreAction()
    {
        $aws = $this->getEnvironment()->aws($this->getParam('cloudLocation'));
        $azlist = $aws->ec2->availabilityZone->describe();
        $zones = array();
        /* @var $az \Scalr\Service\Aws\Ec2\DataType\AvailabilityZoneData */
        foreach ($azlist as $az) {
            if (stristr($az->zoneState, 'available')) {
                $zones[] = array(
                    'id'   => $az->zoneName,
                    'name' => $az->zoneName,
                );
            }
        }
        $this->response->page('ui/tools/aws/rds/instances/restore.js', array('zones' => $zones));
    }

    public function xRestoreInstanceAction()
    {
        $aws = $this->getEnvironment()->aws($this->getParam('cloudLocation'));
        $request = new \Scalr\Service\Aws\Rds\DataType\RestoreDBInstanceFromDBSnapshotRequestData(
            $this->getParam('DBInstanceIdentifier'),
            $this->getParam('Snapshot')
        );
        $request->dBInstanceClass = $this->getParam('DBInstanceClass') ?: null;
        $request->port = $this->getParam('Port') ?: null;
        $request->availabilityZone = $this->getParam('AvailabilityZone') ?: null;
        $request->multiAZ = $this->getParam('MultiAZ') ? 'true' : null;

        $aws->rds->dbInstance->restoreFromSnapshot($request);

        $this->response->success("DB Instance successfully restore from Snapshot");
    }
}
