<?php
use \Scalr\Service\Aws\Rds\DataType\ParameterData;

class Scalr_UI_Controller_Tools_Aws_Rds_Pg extends Scalr_UI_Controller
{
    public function viewAction()
    {
        $this->response->page('ui/tools/aws/rds/pg/view.js', array(
            'locations'	=> self::loadController('Platforms')->getCloudLocations(SERVER_PLATFORMS::EC2, false)
        ));
    }

    public function xListAction()
    {
        $aws = $this->getEnvironment()->aws($this->getParam('cloudLocation'));

        $groupList = $aws->rds->dbParameterGroup->describe();
        $groups = array();
        /* @var $pargroup \Scalr\Service\Aws\Rds\DataType\DBParameterGroupData */
        foreach ($groupList as $pargroup){
            $groups[] = array(
                'Engine'               => $pargroup->dBParameterGroupFamily,
                'DBParameterGroupName' => $pargroup->dBParameterGroupName,
                'Description'          => $pargroup->description,
            );
        }

        $response = $this->buildResponseFromData($groups, array('Description', 'DBParameterGroupName'));

        $this->response->data($response);
    }

    public function xCreateAction()
    {
        $aws = $this->getEnvironment()->aws($this->getParam('cloudLocation'));
        $aws->rds->dbParameterGroup->create(new Scalr\Service\Aws\Rds\DataType\DBParameterGroupData(
            $this->getParam('dbParameterGroupName'),
            $this->getParam('Engine'),
            $this->getParam('Description')
        ));

        $this->response->success("DB parameter group successfully created");
    }

    public function xDeleteAction()
    {
        $aws = $this->getEnvironment()->aws($this->getParam('cloudLocation'));
        $aws->rds->dbParameterGroup->delete($this->getParam('name'));
        $this->response->success("DB parameter group successfully removed");
    }

    public function editAction()
    {
        $aws = $this->getEnvironment()->aws($this->getParam('cloudLocation'));

        $params = $aws->rds->dbParameterGroup->describeParameters($this->getParam('name'));

        $groups = $aws->rds->dbParameterGroup->describe($this->getParam('name'))->get(0);

        $items = array();
        /* @var $value ParameterData */
        foreach ($params as $value) {
            $value = $value->toArray();
            $value = array_combine(array_map('ucfirst', array_keys($value)), array_values($value));
            $itemField = new stdClass();
            if (strpos($value['AllowedValues'], ',') && $value['DataType'] != 'boolean') {
                    $store = explode(',', $value['AllowedValues']);
                    $itemField->xtype = 'combo';
                    $itemField->allowBlank = true;
                    $itemField->editable = false;
                    $itemField->queryMode = 'local';
                    $itemField->displayField = 'name';
                    $itemField->valueField = 'name';
                    $itemField->store = $store;
            } else if($value['DataType'] == 'boolean') {
                $itemField->xtype = 'checkbox';
                $itemField->inputValue = 1;
                $itemField->checked = ($value['ParameterValue'] == 1);
            } else {
                if ($value['IsModifiable'] === false)
                    $itemField->xtype = 'displayfield';
                else
                    $itemField->xtype = 'textfield';
            }
            $itemField->name = $value['Source'] . '[' . $value['ParameterName'] . ']';
            $itemField->fieldLabel = $value['ParameterName'];
            $itemField->value = $value['ParameterValue'];
            $itemField->labelWidth = 250;
            $itemField->width = 790;
            $itemField->readOnly = ($value['IsModifiable'] === false && $itemField->xtype != 'displayfield') ? true : false;

            $itemDesc = new stdClass();
            $itemDesc->xtype = 'displayinfofield';
            $itemDesc->width = 16;
            $itemDesc->margin = '0 0 0 5';
            $itemDesc->info = $value['Description'];

            $item = new stdClass();
            $item->xtype = 'fieldcontainer';
            $item->layout = 'hbox';
            $item->items = array(
                $itemField,
                $itemDesc
            );

            $items[$value['Source']][] = $item;
        }
        $this->response->page('ui/tools/aws/rds/pg/edit.js', array('params' => $items, 'group' => $groups));
    }

    public function xSaveAction()
    {
        $aws = $this->getEnvironment()->aws($this->getParam('cloudLocation'));

        $params = $aws->rds->dbParameterGroup->describeParameters($this->getParam('name'));

        $modifiedParameters = new \Scalr\Service\Aws\Rds\DataType\ParameterList();
        $newParams = array();
        foreach ($this->getParam('system') as $system => $f) {
            $newParams[] = new Scalr\Service\Aws\Rds\DataType\ParameterData($system, null, $f);
        }
        foreach ($this->getParam('engine-default') as $default => $f) {
            $newParams[] = new Scalr\Service\Aws\Rds\DataType\ParameterData($default, null, $f);
        }
        foreach ($this->getParam('user') as $user => $f) {
            $newParams[] = new Scalr\Service\Aws\Rds\DataType\ParameterData($user, null, $f);
        }
        //This piece of code needs to be optimized.
        foreach ($newParams as $newParam) {
            /* @var $newParam ParameterData */
            foreach ($params as $param) {
                /* @var $param ParameterData */
                if ($param->parameterName == $newParam->parameterName) {
                    if ((empty($param->parameterValue) && !empty($newParam->parameterValue)) ||
                        (!empty($param->parameterValue) && empty($newParam->parameterValue)) ||
                        ($newParam->parameterValue !== $param->parameterValue &&
                        !empty($newParam->parameterValue) && !empty($param->parameterValue))
                    ) {
                        if ($param->applyType === 'static') {
                            $newParam->applyMethod = ParameterData::APPLY_METHOD_PENDING_REBOOT;
                        } else {
                            $newParam->applyMethod = ParameterData::APPLY_METHOD_IMMEDIATE;
                        }
                        $modifiedParameters->append($newParam);
                    }
                }
            }
        }
        $oldBoolean = array();
        foreach ($params as $param) {
            if ($param->dataType == 'boolean' && $param->parameterValue == 1) {
                if ($param->applyType == 'static')
                    $param->applyMethod = ParameterData::APPLY_METHOD_PENDING_REBOOT;
                else
                    $param->applyMethod = ParameterData::APPLY_METHOD_IMMEDIATE;
                $oldBoolean[] = $param;
            }
        }
        foreach ($oldBoolean as $old) {
            $found = false;
            foreach ($newParams as $newParam) {
                if ($old->parameterName == $newParam->parameterName)
                    $found = true;
            }
            if (!$found) {
                $old->parameterValue = 0;
                $modifiedParameters->append($old);
            }
        }
        if (count($modifiedParameters)) {
            $aws->rds->dbParameterGroup->modify($this->getParam('name'), $modifiedParameters);
        }
        $this->response->success("DB parameter group successfully updated");
    }

    public function xResetAction()
    {
        $aws = $this->getEnvironment()->aws($this->getParam('cloudLocation'));

        $params = $aws->rds->dbParameterGroup->describeParameters($this->getParam('name'));

        $modifiedParameters = new \Scalr\Service\Aws\Rds\DataType\ParameterList();
        foreach ($params as $param) {
            if ($param->parameterValue && !empty($param->parameterValue)) {
                if ($param->applyType == 'static')
                    $modifiedParameters->append(new ParameterData($param->parameterName, ParameterData::APPLY_METHOD_PENDING_REBOOT, $param->parameterValue));
                else
                    $modifiedParameters->append(new ParameterData($param->parameterName, ParameterData::APPLY_METHOD_IMMEDIATE, $param->parameterValue));
            }
        }
        if (count($modifiedParameters)) {
            $aws->rds->dbParameterGroup->reset($this->getParam('name'), $modifiedParameters);
        }
        $this->response->success("DB parameter group successfully reset to default");
    }
}