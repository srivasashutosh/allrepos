<?php

class UsageStatsPollerProcess implements \Scalr\System\Pcntl\ProcessInterface
{
    public $ThreadArgs;
    public $ProcessDescription = "Farm usage stats poller";
    public $Logger;
    public $IsDaemon;

    public function __construct()
    {
        // Get Logger instance
        $this->Logger = Logger::getLogger(__CLASS__);
    }

    public function OnStartForking()
    {
        $db = \Scalr::getDb();

        $this->Logger->info("Fetching running farms...");

        $this->ThreadArgs = $db->GetAll("SELECT farms.id as id FROM farms
            INNER JOIN clients ON clients.id = farms.clientid WHERE clients.status='Active' AND farms.status=?",
            array(FARM_STATUS::RUNNING)
        );

        $this->Logger->info("Found ".count($this->ThreadArgs)." farms.");
    }

    public function OnEndForking()
    {
    }

    public function StartThread($farminfo)
    {
        $db = \Scalr::getDb();

        $DBFarm = DBFarm::LoadByID($farminfo['id']);

        foreach ($DBFarm->GetFarmRoles() as $DBFarmRole)
        {
            foreach ($DBFarmRole->GetServersByFilter(array('status' => array(SERVER_STATUS::INIT, SERVER_STATUS::RUNNING, SERVER_STATUS::PENDING, SERVER_STATUS::TROUBLESHOOTING)), array()) as $DBServer)
            {
                $launchTime = strtotime($DBServer->dateAdded);
                $lastCheckTime = (int)$DBServer->GetProperty(SERVER_PROPERTIES::STATISTICS_LAST_CHECK_TS);
                if (!$lastCheckTime)
                    $lastCheckTime = $launchTime;

                $period = round((time()-$lastCheckTime) / 60);

                $maxMinutes = (date("j")*24*60) - (date("H")*60);
                if ($period > $maxMinutes)
                    $period = $maxMinutes;

                $serverType = $DBServer->GetFlavor();

                if (!$serverType)
                    continue;

                $db->Execute("INSERT INTO servers_stats SET
                    `usage` = ?,
                    `instance_type` = ?,
                    `env_id` = ?,
                    `month` = ?,
                    `year` = ?,
                    `farm_id` = ?,
                    `cloud_location` = ?
                ON DUPLICATE KEY UPDATE `usage` = `usage` + ?
                ", array(
                    $period,
                    $serverType,
                    $DBServer->envId,
                    date("m"),
                    date("Y"),
                    $DBServer->farmId,
                    $DBServer->GetCloudLocation(),
                    $period
                ));

                $DBServer->SetProperty(SERVER_PROPERTIES::STATISTICS_LAST_CHECK_TS, time());
            } //for each items
        }
    }
}
