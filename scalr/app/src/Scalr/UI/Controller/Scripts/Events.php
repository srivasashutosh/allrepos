<?php
class Scalr_UI_Controller_Scripts_Events extends Scalr_UI_Controller
{
    const CALL_PARAM_NAME = 'eventId';

    public function defaultAction()
    {
        $this->viewAction();
    }

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

    public function createAction()
    {
        $this->response->page('ui/scripts/events/create.js');
    }

    public function xSaveAction()
    {
        $this->request->defineParams(array(
            'eventId', 'name', 'description'
        ));

        if (!preg_match("/^[A-Za-z0-9]+$/si", $this->getParam('name')))
            throw new Exception("Name should contain only alphanumeric characters");

        if (strlen($this->getParam('name')) > 25)
            throw new Exception("Name should be less than 25 characters");

        if (in_array($this->getParam('name'), array_keys(EVENT_TYPE::getScriptingEvents())))
            throw new Exception(sprintf("'%' is reserved name for event. Please select another one.", $this->getParam('name')));

        if (!$this->getParam('eventId'))
            $this->db->Execute("INSERT INTO event_definitions SET
                name = ?,
                description = ?,
                env_id = ?
            ", array(
                $this->getParam('name'),
                $this->getParam('description'),
                $this->getEnvironmentId()
            ));
        else {
            $this->db->Execute("UPDATE event_definitions SET
                name = ?,
                description = ?
            WHERE
                env_id = ? AND id = ?
            ", array(
                $this->getParam('name'),
                $this->getParam('description'),
                $this->getEnvironmentId(),
                $this->getParam('eventId')
            ));
        }

        $this->response->success('Custom event definition successfully saved');
    }

    public function editAction()
    {
        $retval = $this->db->GetRow('SELECT * FROM event_definitions WHERE `id` = ?', array(
            $this->getParam('eventId')
        ));

        if (!$this->user->getPermissions()->hasAccessEnvironment($retval['env_id']))
            throw new Scalr_Exception_InsufficientPermissions();

        $this->response->page('ui/scripts/events/create.js', $retval);
    }

    public function viewAction()
    {
        $this->response->page('ui/scripts/events/view.js');
    }

    public function xListCustomEventsAction()
    {
        $this->request->defineParams(array(
            'sort' => array('type' => 'string', 'default' => 'id'),
            'dir' => array('type' => 'string', 'default' => 'DESC')
        ));

        $sql = "SELECT * FROM event_definitions WHERE env_id='".$this->getEnvironmentId()."'";

        $response = $this->buildResponseFromSql($sql, array('name', 'description'));

        $this->response->data($response);
    }
}
