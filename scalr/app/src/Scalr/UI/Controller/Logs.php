<?php

class Scalr_UI_Controller_Logs extends Scalr_UI_Controller
{
    public function systemAction()
    {
        $farms = self::loadController('Farms')->getList();
        array_unshift($farms, array('id' => 0, 'name' => 'All farms'));

        $this->response->page('ui/logs/system.js', array(
            'farms' => $farms,
            'params' => array(
                'severity[1]' => 0,
                'severity[2]' => 1,
                'severity[3]' => 1,
                'severity[4]' => 1,
                'severity[5]' => 1
            )
        ));
    }

    public function scriptingAction()
    {
        $farms = self::loadController('Farms')->getList();
        array_unshift($farms, array('id' => '0', 'name' => 'All farms'));

        $this->response->page('ui/logs/scripting.js', array(
            'farms' => $farms
        ));
    }

    public function apiAction()
    {
        $this->response->page('ui/logs/api.js');
    }

    public function xListLogsAction()
    {
        $this->request->defineParams(array(
            'serverId' => array('type' => 'string'),
            'farmId' => array('type' => 'int'),
            'severity' => array('type' => 'array'),
            'query' => array('type' => 'string'),
            'sort' => array('type' => 'string', 'default' => 'id'),
            'dir' => array('type' => 'string', 'default' => 'DESC')
        ));

        $farms = $this->db->GetAll("SELECT id FROM farms WHERE env_id=?", array($this->getEnvironmentId()));
        $frms = array();
        foreach ($farms as $f)
            array_push($frms, $f['id']);

        $frms = implode(',', array_values($frms));

        if ($frms == '')
            $frms = '0';

        $authSql = " AND farmid IN ({$frms}) AND farmid > 0";
        $sql = "SELECT * FROM logentries WHERE id > 0 {$authSql}";

        if ($this->getParam('serverId'))
        {
            $serverId = preg_replace("/[^A-Za-z0-9-]+/si", "", $this->getParam('serverId'));
            $sql  .= " AND serverid = '{$serverId}'";
        }

        if ($this->getParam('farmId'))
        {
            $sql  .= " AND farmid = '{$this->getParam('farmId')}'";
        }

        if ($this->getParam('severity'))
        {
            $severities = array();
            foreach ($this->getParam('severity') as $key => $value) {
                if ($value == 1)
                    $severities[] = $key;
            }
            if (count($severities)) {
                $severities = implode(",", $severities);
                $sql  .= " AND severity IN ($severities)";
            } else {
                $sql .= " AND 0"; // is it right ?
            }
        }

        $severities = array(1 => "Debug", 2 => "Info", 3 => "Warning", 4 => "Error", 5 => "Fatal");
        if ($this->getParam('action') == "download") {
            $fileContent = array();
            $farmNames = array();
            $fileContent[] = "Type;Time;Farm;Caller;Message\r\n";

            $response = $this->buildResponseFromSql($sql, array("message", "serverid", "source"), "", true, true);

            foreach($response["data"] as &$data) {
                $data["time"] = Scalr_Util_DateTime::convertTz((int)$data["time"]);
                $data["s_severity"] = $severities[$data["severity"]];

                if (!$farmNames[$data['farmid']])
                    $farmNames[$data['farmid']] = $this->db->GetOne("SELECT name FROM farms WHERE id=?", array($data['farmid']));

                $data['farm_name'] = $farmNames[$data['farmid']];

                $data['message'] = str_replace("<br />","",$data['message']);
                $data['message'] = str_replace("\n","",$data['message']);

                $fileContent[] = "{$data['s_severity']};{$data['time']};{$data['farm_name']};{$data['source']};{$data['message']}";
            }

            $this->response->setHeader('Content-Encoding', 'utf-8');
            $this->response->setHeader('Content-Type', 'text/csv', true);
            $this->response->setHeader('Expires', 'Mon, 10 Jan 1997 08:00:00 GMT');
            $this->response->setHeader('Pragma', 'no-cache');
            $this->response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate');
            $this->response->setHeader('Cache-Control', 'post-check=0, pre-check=0');
            $this->response->setHeader('Content-Disposition', 'attachment; filename=' . "EventLog_" . Scalr_Util_DateTime::convertTz(time(), 'M_j_Y_H:i:s') . ".csv");
            $this->response->setResponse(implode("\n", $fileContent));
        } else {
            $farmNames = array();

            $response = $this->buildResponseFromSql($sql, array("message", "serverid", "source"));

            foreach ($response["data"] as &$row) {
                $row["time"] = Scalr_Util_DateTime::convertTz((int)$row["time"]);

                $row["servername"] = $row["serverid"];
                $row["s_severity"] = $severities[$row["severity"]];
                $row["severity"] = (int)$row["severity"];

                if (!isset($farmNames[$row['farmid']]))
                    $farmNames[$row['farmid']] = $this->db->GetOne("SELECT name FROM farms WHERE id=?", array($row['farmid']));

                $row['farm_name'] = $farmNames[$row['farmid']];

                $row['message'] = nl2br(htmlspecialchars($row['message']));
            }

            $this->response->data($response);
        }
    }

    public function xListScriptingLogsAction()
    {
        $this->request->defineParams(array(
            'farmId' => array('type' => 'int'),
            'serverId' => array('type' => 'string'),
            'query' => array('type' => 'string'),
            'sort' => array('type' => 'string', 'default' => 'id'),
            'dir' => array('type' => 'string', 'default' => 'DESC')
        ));

        $farms = array();
        foreach ($this->db->getAll('SELECT id FROM farms WHERE env_id = ?', array($this->getEnvironmentId())) as $value)
            $farms[] = $value['id'];

        $sql = "SELECT * FROM scripting_log WHERE 1";

        if ($this->getParam('serverId')) {
            $sql  .= " AND server_id = ".$this->db->qstr($this->getParam('serverId'));
        }
        else {
            if ($this->getParam('farmId'))
                $sql .= " AND farmid = '{$this->getParam('farmId')}'";
            else
                $sql .= count($farms) ? " AND farmid IN (" . implode(',', $farms) . ")" : " AND 0";
        }

        if ($this->getParam('eventId'))
            $sql  .= " AND event_id = ".$this->db->qstr($this->getParam('eventId'));

        $this->response->varDump($sql);

        $response = $this->buildResponseFromSql($sql, array("script_name", "server_id", "event_server_id", "event"));
        $cache = array();
        foreach ($response["data"] as &$row) {

            //
            //target_data
            //
            if (!$cache['farm_names'][$row['farmid']])
                $cache['farm_names'][$row['farmid']] = $this->db->GetOne("SELECT name FROM farms WHERE id=?", array($row['farmid']));
            $row['target_farm_name'] = $cache['farm_names'][$row['farmid']];
            $row['target_farm_id'] = $row['farmid'];

            $sInfo = $this->db->GetRow("SELECT role_id, farm_roleid, `index` FROM servers WHERE server_id = ?", array($row['server_id']));
            $row['target_farm_roleid'] = $sInfo['farm_roleid'];

            if (!$cache['role_names'][$sInfo['role_id']])
                $cache['role_names'][$sInfo['role_id']] = $this->db->GetOne("SELECT name FROM roles WHERE id=?", array($sInfo['role_id']));
            $row['target_role_name'] = $cache['role_names'][$sInfo['role_id']];

            $row['target_server_index'] = $sInfo['index'];
            $row['target_server_id'] = $row['server_id'];


            //
            //event_data
            //
            if ($row['event_server_id']) {
                $esInfo = $this->db->GetRow("SELECT role_id, farm_roleid, `index`, farm_id FROM servers WHERE server_id = ?", array($row['event_server_id']));

                if (!$cache['farm_names'][$esInfo['farm_id']])
                    $cache['farm_names'][$esInfo['farm_id']] = $this->db->GetOne("SELECT name FROM farms WHERE id=?", array($esInfo['farm_id']));
                $row['event_farm_name'] = $cache['farm_names'][$esInfo['farm_id']];
                $row['event_farm_id'] = $esInfo['farm_id'];

                $row['event_farm_roleid'] = $esInfo['farm_roleid'];

                if (!$cache['role_names'][$esInfo['role_id']])
                    $cache['role_names'][$esInfo['role_id']] = $this->db->GetOne("SELECT name FROM roles WHERE id=?", array($esInfo['role_id']));
                $row['event_role_name'] = $cache['role_names'][$esInfo['role_id']];

                $row['event_server_index'] = $esInfo['index'];
            }

            $row['dtadded'] = Scalr_Util_DateTime::convertTz($row['dtadded']);
            $row['message'] = nl2br(htmlspecialchars($row['message']));
        }

        $this->response->data($response);
    }

    public function xListApiLogsAction()
    {
        $this->request->defineParams(array(
            'sort' => array('type' => 'string', 'default' => 'id'),
            'dir' => array('type' => 'string', 'default' => 'DESC')
        ));

        $sql = "SELECT * from api_log WHERE id > 0 AND clientid='{$this->user->getAccountId()}'";

        $response = $this->buildResponseFromSql($sql, array("transaction_id"));
        foreach ($response["data"] as &$row) {
            $row["dtadded"] = Scalr_Util_DateTime::convertTz((int)$row["dtadded"]);
        }

        $this->response->data($response);
    }

    // @DEPRECATED 4.0
    public function scriptingMessageAction()
    {
        $entry = $this->db->GetRow("SELECT * FROM scripting_log WHERE id = ?", array($this->getParam('eventId')));
        if (empty($entry))
            throw new Exception ('Unknown event');

        $farm = DBFarm::LoadByID($entry['farmid']);
        $this->user->getPermissions()->validate($farm);

        $form = array(
            array(
                'xtype' => 'fieldset',
                'title' => 'Message',
                'layout' => 'fit',
                'items' => array(
                    array(
                        'xtype' => 'textarea',
                        'readOnly' => true,
                        'hideLabel' => true,
                        'height' => 400,
                        'value' => $entry['message']
                    )
                )
            )
        );

        $this->response->page('ui/logs/scriptingmessage.js', $form);
    }

    public function apiLogEntryDetailsAction()
    {
        $entry = $this->db->GetRow("SELECT * FROM api_log WHERE transaction_id = ? AND clientid = ?", array($this->getParam('transactionId'), $this->user->getAccountId()));
        if (empty($entry))
            throw new Exception ('Unknown transaction');

        $entry['dtadded'] = Scalr_Util_DateTime::convertTz((int)$entry['dtadded']);

        $form = array(
            array(
                'xtype' => 'fieldset',
                'title' => 'General information',
                'labelWidth' => 120,
                'items' => array(
                    array(
                        'xtype' => 'displayfield',
                        'fieldLabel' => 'Transaction ID',
                        'value' => $entry['transaction_id']
                    ),
                    array(
                        'xtype' => 'displayfield',
                        'fieldLabel' => 'Action',
                        'value' => $entry['action']
                    ),
                    array(
                        'xtype' => 'displayfield',
                        'fieldLabel' => 'IP address',
                        'value' => $entry['ipaddress']
                    ),
                    array(
                        'xtype' => 'displayfield',
                        'fieldLabel' => 'Time',
                        'value' => $entry['dtadded']
                    )
                )
            ),
            array(
                'xtype' => 'fieldset',
                'title' => 'Request',
                'layout' => 'fit',
                'items' => array(
                    array(
                        'xtype' => 'textarea',
                        'grow' => true,
                        'growMax' => 200,
                        'readOnly' => true,
                        'hideLabel' => true,
                        'value' => $entry['request']
                    )
                )
            ),
            array(
                'xtype' => 'fieldset',
                'title' => 'Response',
                'layout' => 'fit',
                'items' => array(
                    array(
                        'xtype' => 'textarea',
                        'grow' => true,
                        'growMax' => 200,
                        'readOnly' => true,
                        'hideLabel' => true,
                        'value' => $entry['response']
                    )
                )
            )
        );

        $this->response->page('ui/logs/apilogentrydetails.js', $form);
    }
}
