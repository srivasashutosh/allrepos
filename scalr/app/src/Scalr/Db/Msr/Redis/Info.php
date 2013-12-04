<?php

class Scalr_Db_Msr_Redis_Info extends Scalr_Db_Msr_Info {

    protected $masterPassword;
    protected $persistenceType;

    public function __construct(DBFarmRole $dbFarmRole, DBServer $dbServer) {

        $this->databaseType = Scalr_Db_Msr::DB_TYPE_REDIS;

        parent::__construct($dbFarmRole, $dbServer);

        $this->masterPassword = $dbFarmRole->GetSetting(Scalr_Db_Msr_Redis::MASTER_PASSWORD);
        $this->persistenceType = $dbFarmRole->GetSetting(Scalr_Db_Msr_Redis::PERSISTENCE_TYPE);
        $this->usePassword = $dbFarmRole->GetSetting(Scalr_Db_Msr_Redis::USE_PASSWORD);

        $this->numProcesses = $dbFarmRole->GetSetting(Scalr_Db_Msr_Redis::NUM_PROCESSES);
        if (!$this->numProcesses)
            $this->numProcesses = 1;

        if ($dbFarmRole->GetSetting(Scalr_Db_Msr_Redis::PORTS_ARRAY))
            $this->ports = @json_decode($dbFarmRole->GetSetting(Scalr_Db_Msr_Redis::PORTS_ARRAY));

        if ($dbFarmRole->GetSetting(Scalr_Db_Msr_Redis::PASSWD_ARRAY))
            $this->passwords = @json_decode($dbFarmRole->GetSetting(Scalr_Db_Msr_Redis::PASSWD_ARRAY));

        if (!$this->ports && $this->masterPassword) {
            $this->ports = array(6379);
            $this->passwords = array($this->masterPassword);
        }
    }

    public function getMessageProperties() {
        $retval = parent::getMessageProperties();

        $retval->masterPassword = $this->masterPassword;
        $retval->persistence_type = $this->persistenceType;
        $retval->use_password = $this->usePassword;

        $retval->num_processes = $this->numProcesses;
        $retval->ports = $this->ports;
        $retval->passwords = $this->passwords;

        return $retval;
    }

    public function setMsrSettings($settings) {

        if ($this->replicationMaster) {
            parent::setMsrSettings($settings);

            $roleSettings = array(
                Scalr_Db_Msr_Redis::MASTER_PASSWORD => $settings->masterPassword
            );

            if ($settings->ports)
                $roleSettings[Scalr_Db_Msr_Redis::PORTS_ARRAY] = @json_encode($settings->ports);

            if ($settings->passwords)
                $roleSettings[Scalr_Db_Msr_Redis::PASSWD_ARRAY] = @json_encode($settings->passwords);

            foreach ($roleSettings as $name=>$value)
                $this->dbFarmRole->SetSetting($name, $value);

        }
    }
}