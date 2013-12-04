<?php
class Scalr_UI_Controller_Dashboard_Widget_Lasterrors extends Scalr_UI_Controller_Dashboard_Widget
{
    public function getDefinition()
    {
        return array(
            'type' => 'local'
        );
    }

    public function getContent($params = array())
    {
        if (!$params['errorCount'])
            $params['errorCount'] = 10;
        $sql = 'SELECT time, message, serverid as server_id  FROM logentries WHERE severity = 4 AND farmid IN (SELECT id FROM farms WHERE env_id = ?) GROUP BY message, source ORDER BY time DESC LIMIT 0, ?';
        $r = $this->db->Execute($sql, array($this->getEnvironmentId(), (int)$params['errorCount']));
        $retval = array();
        while ($value = $r->FetchRow()) {
            $value['message'] = htmlspecialchars($value['message']);
            $value['time'] = date('H:i:s, M d',$value["time"]);
            $retval[] = $value;
        }
        return $retval;
    }
}
