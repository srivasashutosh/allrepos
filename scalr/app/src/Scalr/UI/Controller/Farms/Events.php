<?php
class Scalr_UI_Controller_Farms_Events extends Scalr_UI_Controller
{
    const CALL_PARAM_NAME = 'eventId';

    /**
     *
     * @var DBFarm
     */
    private $dbFarm;

    public function init()
    {
        $this->dbFarm = DBFarm::LoadByID($this->getParam(Scalr_UI_Controller_Farms::CALL_PARAM_NAME));
        $this->user->getPermissions()->validate($this->dbFarm);
    }

    public function defaultAction()
    {
        $this->viewAction();
    }

    public function viewAction()
    {
        $this->response->page('ui/farms/events/view.js', array(
            'farmName' => $this->dbFarm->Name
        ));
    }

    public function xListEventsAction()
    {
        $this->request->defineParams(array(
            'farmId' => array('type' => 'int'),
            'eventServerId',
            'eventId',
            'query' => array('type' => 'string'),
            'sort' => array('type' => 'string', 'default' => 'id'),
            'dir' => array('type' => 'string', 'default' => 'DESC')
        ));

        $sql = "SELECT farmid, message, type, dtadded, event_server_id, event_id FROM events WHERE farmid='{$this->dbFarm->ID}'";

        if ($this->getParam('eventServerId'))
            $sql .= " AND event_server_id = ".$this->db->qstr($this->getParam('eventServerId'));

        if ($this->getParam('eventId'))
            $sql .= " AND event_id = ".$this->db->qstr($this->getParam('eventId'));

        $response = $this->buildResponseFromSql($sql, array("message", "type", "dtadded", "event_server_id", "event_id"));

        $cache = array();

        foreach ($response['data'] as &$row) {
            $row['message'] = nl2br($row['message']);
            $row["dtadded"] = Scalr_Util_DateTime::convertTz($row["dtadded"]);

            $row['scripts'] = $this->db->GetOne("SELECT COUNT(*) FROM scripting_log WHERE event_id = ?", array($row['event_id']));

            if ($row['event_server_id']) {
                $esInfo = $this->db->GetRow("SELECT role_id, farm_roleid, `index`, farm_id FROM servers WHERE server_id = ?", array($row['event_server_id']));

                if ($esInfo) {
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
            }
        }

        $this->response->data($response);
    }
    public function configureAction()
    {
        $observers = array('MailEventObserver','RESTEventObserver');
        $form = array();

        foreach ($observers as $observer)
        {
            $observerItems = $observer::GetConfigurationForm();
            $farm_observer_id = $this->db->GetOne("SELECT id FROM farm_event_observers
                WHERE farmid=? AND event_observer_name=?",
                array($this->getParam('farmId'), $observer)
            );
            if ($farm_observer_id)
            {
                $config_opts = $this->db->Execute("SELECT * FROM farm_event_observers_config
                    WHERE observerid=?", array($farm_observer_id)
                );
                while($config_opt = $config_opts->FetchRow())
                {
                    $field = &$observerItems->GetFieldByName($config_opt['key']);
                    if ($field)
                        $field->Value = $config_opt['value'];
                }
            }

            foreach ($observerItems->ListFields() as $observerItem)
                $form[$observer][] = $observerItem;
        }
        $this->response->page('ui/farms/events/configure.js', array('farmName' => $this->dbFarm->Name, 'form' => $form));
    }
    public function xSaveNotificationsAction()
    {
        $this->request->defineParams(array(
            'MailEventObserver' => array('type' => 'json'),
            'RESTEventObserver' => array('type' => 'json')
        ));
        $observers = array('MailEventObserver','RESTEventObserver');

        foreach ($observers as $observer)
        {
            $farm_observer_id = $this->db->GetOne("SELECT id FROM farm_event_observers
                WHERE farmid=? AND event_observer_name=?",
                array($this->getParam('farmId'), $observer)
            );
            if($farm_observer_id) //if exist in database
            {
                $this->db->Execute("DELETE FROM farm_event_observers_config WHERE observerid = ?",
                    array($farm_observer_id)
                );
            }
            else if(!$farm_observer_id) // if not in database
            {
                // insert
                $this->db->Execute("INSERT INTO farm_event_observers SET farmid=?, event_observer_name=?",
                    array($this->getParam('farmId'), $observer)
                );
                $farm_observer_id = $this->db->Insert_ID();
            }
            if($this->getParam($observer.'Enabled'))
            {
                //update
                $this->db->Execute("INSERT INTO farm_event_observers_config SET
                        `key` =?,
                        `value` = ?,
                        `observerid` = ?",
                        array('IsEnabled', '1', $farm_observer_id));

                foreach ($this->getParam($observer) as $observItem){
                    //set params
                    if(!empty($observItem['value']))
                    $this->db->Execute("INSERT INTO farm_event_observers_config SET
                        `key` =?,
                        `value` = ?,
                        `observerid` = ?",
                        array($observItem['name'], $observItem['value'], $farm_observer_id));
                }
            }
        }
        $this->response->success('Notification settings successfully updated');
    }
}
