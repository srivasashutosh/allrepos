<?php

use \Scalr\Server\Alerts;

class Scalr_UI_Controller_Alerts extends Scalr_UI_Controller
{
    const CALL_PARAM_NAME = 'alertId';

    public function defaultAction()
    {
        $this->viewAction();
    }

    /*
    public function xRemoveAction()
    {
        $this->request->defineParams(array(
            'events' => array('type' => 'json')
        ));

        foreach ($this->getParam('events') as $id)
        {
            $this->db->Execute("DELETE FROM event_definitions WHERE env_id=? AND id=?",
                array($this->getEnvironmentId(), $id)
            );
        }

        $this->response->success();
    }
    */

    public function viewAction()
    {
        $this->response->page('ui/alerts/view.js');
    }

    public function xListAlertsAction()
    {
        $this->request->defineParams(array(
            'sort' => array('type' => 'string', 'default' => 'id'),
            'dir' => array('type' => 'string', 'default' => 'DESC')
        ));

        $sql = "SELECT * FROM server_alerts WHERE env_id='".$this->getEnvironmentId()."'";

        if ($this->getParam('serverId')) {
            $sql .= ' AND server_id = '.$this->db->qstr($this->getParam('serverId'));
        }

        if ($this->getParam('farmId')) {
            $sql .= ' AND farm_id = '.$this->db->qstr($this->getParam('farmId'));

            if ($this->getParam('farmRoleId')) {
                $sql .= ' AND farm_roleid = '.$this->db->qstr($this->getParam('farmRoleId'));
            }
        }

        if ($this->getParam('status')) {
            $sql .= ' AND status = '.$this->db->qstr($this->getParam('status'));
        }

        $response = $this->buildResponseFromSql($sql, array('server_id', 'details'));

        foreach ($response['data'] as $i => $row) {
            $row['dtoccured'] = Scalr_Util_DateTime::convertTz($row['dtoccured']);

            if ($row['dtlastcheck'])
                $row['dtlastcheck'] = Scalr_Util_DateTime::convertTz($row['dtlastcheck']);
            else
                $row['dtlastcheck'] = false;

            if ($row['status'] == Alerts::STATUS_RESOLVED)
                $row['dtsolved'] = Scalr_Util_DateTime::convertTz($row['dtsolved']);
            else
                $row['dtsolved'] = false;

            $row['metric'] = Alerts::getMetricName($row['metric']);

            $row['farm_name'] = DBFarm::LoadByID($row['farm_id'])->Name;

            try {
                $row['role_name'] = DBFarmRole::LoadByID($row['farm_roleid'])->GetRoleObject()->name;

                $dbServer = DBServer::LoadByID($row['server_id']);
                $row['server_exists'] = ($dbServer->status == SERVER_STATUS::RUNNING) ? true : false;
            } catch (Exception $e) {

            }

            $response['data'][$i] = $row;
        }

        $this->response->data($response);
    }
}
