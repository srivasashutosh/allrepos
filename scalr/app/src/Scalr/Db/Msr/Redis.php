<?php
class Scalr_Db_Msr_Redis extends Scalr_Db_Msr
{
    /* DBFarmRole settings */
    const MASTER_PASSWORD = 'db.msr.redis.master_password';
    const PERSISTENCE_TYPE = 'db.msr.redis.persistence_type';
    const USE_PASSWORD = 'db.msr.redis.use_password';

    const NUM_PROCESSES = 'db.msr.redis.num_processes';
    const PORTS_ARRAY = 'db.msr.redis.ports';
    const PASSWD_ARRAY = 'db.msr.redis.passwords';
}