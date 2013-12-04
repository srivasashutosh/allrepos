<?php

class Scalr_UI_Controller_Tools_Aws_Rds_Snapshots extends Scalr_UI_Controller
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
        $this->response->page('ui/tools/aws/rds/snapshots.js', array(
            'locations' => self::loadController('Platforms')->getCloudLocations(SERVER_PLATFORMS::EC2, false)
        ));
    }

    public function xListSnapshotsAction()
    {
        $this->request->defineParams(array(
            'cloudLocation', 'dbinstance',
            'sort' => array('type' => 'json', 'default' => array('property' => 'id', 'direction' => 'ASC'))
        ));

        $aws = $this->getEnvironment()->aws($this->getParam('cloudLocation'));

        $rows = $aws->rds->dbSnapshot->describe($this->getParam('dbinstance'));
        $rowz = array();
        /* @var $pv \Scalr\Service\Aws\Rds\DataType\DBSnapshotData */
        foreach ($rows as $pv)
            $rowz[] = array(
                "dtcreated"		=> $pv->snapshotCreateTime,
                "port"			=> $pv->port,
                "status"		=> $pv->status,
                "engine"		=> $pv->engine,
                "avail_zone"	=> $pv->availabilityZone,
                "idtcreated"	=> $pv->instanceCreateTime,
                "storage"		=> $pv->allocatedStorage,
                "name"			=> $pv->dBSnapshotIdentifier,
                "id"			=> $pv->dBSnapshotIdentifier,
            );

        $response = $this->buildResponseFromData($rowz);
        foreach ($response['data'] as &$row) {
            $row['dtcreated'] = $row['dtcreated'] ? Scalr_Util_DateTime::convertTz($row['dtcreated']) : '';
            $row['idtcreated'] = $row['idtcreated'] ? Scalr_Util_DateTime::convertTz($row['idtcreated']) : '';
        }
        $this->response->data($response);
    }

    public function xCreateSnapshotAction()
    {
        $aws = $this->getEnvironment()->aws($this->getParam('cloudLocation'));

        $snapId = "scalr-manual-" . dechex(microtime(true)*10000) . rand(0,9);

        try {
            $aws->rds->dbInstance->createSnapshot($this->getParam('dbinstance'), $snapId);
            $this->db->Execute("
                INSERT INTO rds_snaps_info SET snapid=?, comment=?, dtcreated=NOW(), region=?
            ", array(
                $snapId, "manual RDS instance snapshot", $this->getParam('cloudLocation')
            ));
        } catch (Exception $e) {
            throw new Exception (sprintf(_("Can't create db snapshot: %s"), $e->getMessage()));
        }

        $this->response->success(sprintf(_("DB snapshot '%s' successfully create"), $snapId));
    }

    public function xDeleteSnapshotsAction()
    {
        $this->request->defineParams(array(
            'snapshots' => array('type' => 'json')
        ));

        $aws = $this->getEnvironment()->aws($this->getParam('cloudLocation'));

        $i = 0;
        $errors = array();
        foreach ($this->getParam('snapshots') as $snapName) {
            try {
                $aws->rds->dbSnapshot->delete($snapName);
                $this->db->Execute("DELETE FROM rds_snaps_info WHERE snapid=? ", array($snapName));
                $i++;
            } catch (Exception $e) {
                $errors[] = sprintf(_("Can't delete db snapshot %s: %s"), $snapName, $e->getMessage());
            }
        }
        $message = sprintf(_("%s db snapshot(s) successfully removed"), $i);

        if (count($errors))
            $this->response->warning(nl2br(implode("\n", (array_merge(array($message), $errors)))));
        else
            $this->response->success($message);
    }
}
