<?php

class Scalr_Db_Msr_Mysql2_Info extends Scalr_Db_Msr_Info {

    protected
        $replPassword,
        $rootPassword,
        $statPassword,
        $logPos,
        $logFile;


    public function __construct(DBFarmRole $dbFarmRole, DBServer $dbServer, $type = null) {

        $this->databaseType = ($type) ? $type : ROLE_BEHAVIORS::MYSQL2;

        parent::__construct($dbFarmRole, $dbServer);

        $this->rootPassword = $dbFarmRole->GetSetting(Scalr_Db_Msr_Mysql2::ROOT_PASSWORD);
        $this->replPassword = $dbFarmRole->GetSetting(Scalr_Db_Msr_Mysql2::REPL_PASSWORD);
        $this->statPassword = $dbFarmRole->GetSetting(Scalr_Db_Msr_Mysql2::STAT_PASSWORD);

        $this->logPos = $dbFarmRole->GetSetting(Scalr_Db_Msr_Mysql2::LOG_POS);
        $this->logFile = $dbFarmRole->GetSetting(Scalr_Db_Msr_Mysql2::LOG_FILE);
    }

    public function getMessageProperties() {
        $retval = parent::getMessageProperties();

        $retval->rootPassword = $this->rootPassword;
        $retval->replPassword = $this->replPassword;
        $retval->statPassword = $this->statPassword;

        $retval->logPos = $this->logPos;
        $retval->logFile = $this->logFile;

        return $retval;
    }

    public function setMsrSettings($settings) {

        if ($this->replicationMaster) {
            parent::setMsrSettings($settings);

            $roleSettings = array(
                Scalr_Db_Msr_Mysql2::REPL_PASSWORD => $settings->replPassword,
                Scalr_Db_Msr_Mysql2::ROOT_PASSWORD => $settings->rootPassword,
                Scalr_Db_Msr_Mysql2::STAT_PASSWORD => $settings->statPassword,
                Scalr_Db_Msr_Mysql2::LOG_POS => $settings->logPos,
                Scalr_Db_Msr_Mysql2::LOG_FILE => $settings->logFile
            );


            foreach ($roleSettings as $name=>$value)
                $this->dbFarmRole->SetSetting($name, $value);
        }
    }
}