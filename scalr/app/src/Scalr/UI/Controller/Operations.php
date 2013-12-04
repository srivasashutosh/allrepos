<?php

class Scalr_UI_Controller_Operations extends Scalr_UI_Controller
{
    const CALL_PARAM_NAME = 'operationId';

    public function defaultAction()
    {
        $this->detailsAction();
    }

    public function detailsAction()
    {
        $opId = $this->getParam('operationId');
        if ($opId) {
            $operation = $this->db->GetRow("SELECT * FROM server_operations WHERE id = ?", array($opId));
            $dbServer = DBServer::LoadByID($operation['server_id']);
            $this->user->getPermissions()->validate($dbServer);

            $opName = $operation['name'];
        } else {
            $serverId = $this->getParam("serverId");
            $opName = $this->getParam("operation");

            $operation = $this->db->GetRow("SELECT * FROM server_operations WHERE server_id = ? AND name = ?", array($serverId, $opName));
            if ($operation) {
                $opId = $operation['id'];
            } else {
                $operation = array("name" => $opName, "status" => "pending", "timestamp" => time(), "phases" => json_encode(array()));
            }

            $dbServer = DBServer::LoadByID($serverId);
            $this->user->getPermissions()->validate($dbServer);
        }

        if (!$dbServer)
            throw new Exception("Operation details not available yet.");

        $details = array();
        $phases = @json_decode($operation['phases']);

        if ($opName == 'Initialization') {
            $launchError = $dbServer->GetProperty(SERVER_PROPERTIES::LAUNCH_ERROR);
            if ($launchError) {
                $details['Server provisioning']['status'] = 'pending';
                $details['Boot OS']['status'] = 'pending';
                $details['Start & Initialize scalarizr']['status'] = 'pending';
                $message = "<span style='color:red;'>Unable to launch instance: {$launchError}</span>";
            } else {
                $details['Server provisioning']['status'] = 'running';
                $details['Boot OS']['status'] = 'pending';
                $details['Start & Initialize scalarizr']['status'] = 'pending';

                try {
                    if ($dbServer && $dbServer->GetRealStatus(true)->isRunning()) {
                        $details['Server provisioning']['status'] = 'complete';


                        if ($dbServer->status == SERVER_STATUS::PENDING) {
                            $details['Boot OS']['status'] = 'running';
                            $details['Start & Initialize scalarizr']['status'] = 'pending';
                        } elseif ($dbServer->status == SERVER_STATUS::INIT) {
                            $details['Boot OS']['status'] = 'complete';
                            $details['Start & Initialize scalarizr']['status'] = 'running';
                        } else {
                            $details['Boot OS']['status'] = 'complete';
                            $details['Start & Initialize scalarizr']['status'] = 'complete';
                        }
                    }
                } catch (Exception $e) {
                    if ($dbServer->status == SERVER_STATUS::PENDING_LAUNCH) {
                        $details['Server provisioning']['status'] = 'running';
                        $details['Boot OS']['status'] = 'pending';
                        $details['Start & Initialize scalarizr']['status'] = 'pending';
                    } else {
                        $details['Server provisioning']['status'] = 'pending';
                        $details['Boot OS']['status'] = 'complete';
                        $details['Start & Initialize scalarizr']['status'] = 'complete';
                    }
                }

                $intSteps = $this->db->GetOne("SELECT COUNT(*) FROM server_operation_progress WHERE operation_id = ? AND phase = ? ORDER BY stepno ASC", array($operation['id'], 'Scalarizr routines'));
                if ($intSteps > 0) {
                    $c = new stdClass();
                    $c->name = "Scalarizr routines";
                    $c->steps = array();
                    array_push($phases, $c);
                }

                if ($dbServer->GetProperty(SERVER_PROPERTIES::SZR_IS_INIT_FAILED)) {
                    $initStatus = '<span style="color:red;">Initialization failed</span>';
                    $message = $dbServer->GetProperty(SERVER_PROPERTIES::SZR_IS_INIT_ERROR_MSG);
                } else
                    $initStatus = $dbServer->status;
            }
        }

        if (!$phases || count($phases) == 0) {
            $p = new stdClass();
            $p->name = $operation['name'];
            $phases = array($p);
        }

        foreach ($phases as $phase) {
            $definedSteps = (array)$phase->steps;
            $stats = array();

            $steps = $this->db->Execute("SELECT step, status, progress, message FROM server_operation_progress WHERE operation_id = ? AND phase = ? ORDER BY stepno ASC", array($operation['id'], $phase->name));
            while ($step = $steps->FetchRow()) {
                $details[$phase->name]['steps'][$step['step']] = array(
                    'status' => $step['status'],
                    'progress' => $step['progress'],
                    'message' => nl2br($step['message']),
                );

                switch($step['status']) {
                    case "running":
                        $stats['pending']--;
                        $stats['running']++;
                    break;
                    case "complete":
                    case "warning":
                        $stats['pending']--;
                        $stats['complete']++;
                    break;
                    case "error":
                        $stats['error']++;
                    break;
                }
            }

            foreach ($definedSteps as $step) {
                if (!$details[$phase->name]['steps'][$step]) {
                    $details[$phase->name]['steps'][$step] = array('status' => 'pending');
                    $stats['pending']++;
                }
            }

            $details[$phase->name]['status'] = 'pending';

            if ($stats['error'] > 0)
                $details[$phase->name]['status'] = 'error';
            elseif ($stats['running'] > 0 || ($stats['pending'] > 0 && $stats['complete'] > 0))
                $details[$phase->name]['status'] = 'running';
            elseif ($stats['pending'] <= 0 && $stats['running'] == 0 && count($details[$phase->name]['steps']) != 0)
                $details[$phase->name]['status'] = 'complete';
        }

        //scalr-operation-status-
        $content = '<div style="margin:10px;">';
        foreach ($details as $phaseName => $phase) {
            $cont = ($phase['status'] != 'running') ? "&nbsp;" : "<img src='/ui2/images/icons/running.gif' />";
            $content .= "<div style='clear:both;'><div class='scalr-operation-status-{$phase['status']}'>{$cont}</div> {$phaseName}</div>";

            if (!empty($phase['steps']) && is_array($phase['steps'])) {
                foreach ($phase['steps'] as $stepName => $step) {
                    $cont = ($step['status'] != 'running') ? "&nbsp;" : "<img src='/ui2/images/icons/running.gif' />";
                    $content .= "<div style='clear:both;padding-left:15px;'><div class='scalr-operation-status-{$step['status']}'>{$cont}</div> {$stepName}</div>";

                    if ($step['status'] == 'error')
                        $message = $step['message'];
                }
            }
        }
        $content .= '</div>';

        $this->response->page('ui/operations/details.js', array(
            'serverId' => $dbServer->serverId,
            'initStatus' => $initStatus,
            'status'	=> $operation['status'],
            'name'		=> $operation['name'],
            'date'		=> Scalr_Util_DateTime::convertTz((int)$operation['timestamp']),
            'content' => $content,
            'message' => $message
        ));
    }
}
