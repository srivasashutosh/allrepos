<?php

abstract class Scalr_Messaging_Msg_DbMsr extends Scalr_Messaging_Msg {
    function __construct () {
        parent::__construct();
    }

    function addDbMsrInfo(Scalr_Db_Msr_Info $msrInfo)
    {
        $this->dbType = $msrInfo->databaseType;
        $this->{$msrInfo->databaseType} = $msrInfo->getMessageProperties();
    }
}
