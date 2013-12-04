<?php

require(dirname(__FILE__)."/../../src/prepend.inc.php");

require_once(dirname(__FILE__)."/../../cron/watchers/class.CPUSNMPWatcher.php");
require_once(dirname(__FILE__)."/../../cron/watchers/class.LASNMPWatcher.php");
require_once(dirname(__FILE__)."/../../cron/watchers/class.MEMSNMPWatcher.php");
require_once(dirname(__FILE__)."/../../cron/watchers/class.NETSNMPWatcher.php");
require_once(dirname(__FILE__)."/../../cron/watchers/class.ServersNumWatcher.php");

if ($_REQUEST['task'] == "get_stats_image_url")
{
    $farmid = (int)$_REQUEST['farmid'];
    $watchername = $_REQUEST['watchername'];
    $graph_type = $_REQUEST['graph_type'];
    $role_name = ($_REQUEST['role_name']) ? $_REQUEST['role_name'] : $_REQUEST['role'];

    if ($_REQUEST['version'] == 2)
    {
        if ($role_name != 'FARM' && !stristr($role_name, "INSTANCE_"))
            $role_name = "FR_{$role_name}";
    }

    $farminfo = $db->GetRow("SELECT status, id, env_id, clientid FROM farms WHERE id=?", array($farmid));
    if ($farminfo["status"] != FARM_STATUS::RUNNING)
        $result = array("success" => false, "msg" => _("Statistics not available for terminated farm"));
    else
    {
        if ($farminfo['clientid'] != 0)
        {
            if (!defined('SCALR_SERVER_TZ'))
                define("SCALR_SERVER_TZ", date("T"));

            $env = Scalr_Model::init(Scalr_Model::ENVIRONMENT)->loadById($farminfo['env_id']);
            $tz = $env->getPlatformConfigValue(ENVIRONMENT_SETTINGS::TIMEZONE);
            if ($tz) {
                date_default_timezone_set($tz);
                putenv(sprintf("TZ=%s", date_default_timezone_get()));
            } else {
                if (defined('SCALR_SERVER_TZ') && SCALR_SERVER_TZ) {
                    date_default_timezone_set(SCALR_SERVER_TZ);
                    putenv(sprintf("TZ=%s", SCALR_SERVER_TZ));
                }
            }
        }

        $graph_info = GetGraphicInfo($graph_type);

        $image_path = APPPATH."/cache/stats/{$farmid}/{$role_name}.{$watchername}.{$graph_type}.gif";

        $lastDigit = substr("{$farminfo['id']}", -1);

        /*
        /mnt/rrddata/x1x6
        /mnt/rrddata/x2x7
        /mnt/rrddata/x3x8
        /mnt/rrddata/x4x9
        /mnt/rrddata/x5x0
         */

        switch ($lastDigit) {
            case 1:
            case 6:
                $folder = "x1x6";
                break;

            case 2:
            case 7:
                $folder = "x2x7";
                break;

            case 3:
            case 8:
                $folder = "x3x8";
                break;

            case 4:
            case 9:
                $folder = "x4x9";
                break;

            case 5:
            case 0:
                $folder = "x5x0";
                break;
        }

        $farm_rrddb_dir = \Scalr::config('scalr.stats_poller.rrd_db_dir') . "/{$folder}/{$farminfo['id']}";

        if ($watchername == 'ServersNum')
            $rrddbpath = "{$farm_rrddb_dir}/{$role_name}/SERVERS/db.rrd";
        else
            $rrddbpath = "{$farm_rrddb_dir}/{$role_name}/{$watchername}/db.rrd";

        if (file_exists($rrddbpath)) {
            try {
                GenerateGraph($farmid, $role_name, $rrddbpath, $watchername, $graph_type, $image_path);

                $url = \Scalr::config('scalr.stats_poller.graphics_url')."/{$farmid}/{$role_name}_{$watchername}.{$graph_type}.gif";

                $result = array("success" => true, "msg" => $url);
            } catch (Exception $e) {
                $result = array(
                    "success" => false,
                    "msg"     => $e->getMessage()
                );
            }
        } else {
            $result = array("success" => false, "msg" => _("Statistics not available yet"));
        }
    }
}

print json_encode($result);

function GenerateGraph($farmid, $role_name, $rrddbpath, $watchername, $graph_type)
{
    $image_path = \Scalr::config('scalr.stats_poller.images_path')
        . "/{$farmid}/{$role_name}_{$watchername}.{$graph_type}.gif";

    @mkdir(dirname($image_path), 0777, true);

    $graph_info = GetGraphicInfo($graph_type);

    if (file_exists($image_path))
    {
        clearstatcache();
        $time = filemtime($image_path);

        if ($time > time()-$graph_info['update_every'])
            return;
    }

    // Plot daily graphic
    try
    {
        $Reflect = new ReflectionClass("{$watchername}Watcher");
        $PlotGraphicMethod = $Reflect->getMethod("PlotGraphic");
        $PlotGraphicMethod->invoke(NULL, $rrddbpath, $image_path, $graph_info);
    }
    catch(Exception $e)
    {
        Logger::getLogger('STATS')->fatal("Cannot plot graphic: {$e->getMessage()}");
        return;
    }
}

function GetGraphicInfo($type)
{
    switch($type)
    {
        case GRAPH_TYPE::DAILY:
            $r = array(
                "start" => "-1d5min",
                "end" => "-5min",
                "step" => 180,
                "update_every" => 600,
                "x_grid" => "HOUR:1:HOUR:2:HOUR:2:0:%H"
            );
            break;
        case GRAPH_TYPE::WEEKLY:
            $r = array(
                "start" => "-1wk5min",
                "end" => "-5min",
                "step" => 1800,
                "update_every" => 7200,
                "x_grid" => "HOUR:12:HOUR:24:HOUR:24:0:%a"
            );
            break;
        case GRAPH_TYPE::MONTHLY:
            $r = array(
                "start" => "-1mon5min",
                "end" => "-5min",
                "step" => 7200,
                "update_every" => 43200,
                "x_grid" => "DAY:2:WEEK:1:WEEK:1:0:week %V"
            );
            break;
        case GRAPH_TYPE::YEARLY:
            $r = array(
                "start" => "-1y",
                "end" => "-5min",
                "step" => 86400,
                "update_every" => 86400,
                "x_grid" => "MONTH:1:MONTH:1:MONTH:1:0:%b"
            );
            break;
    }

    return $r;
}
