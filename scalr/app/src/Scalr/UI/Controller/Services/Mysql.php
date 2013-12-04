<?php

class Scalr_UI_Controller_Services_Mysql extends Scalr_UI_Controller
{
    private function pmaEncrypt($input, $key)
    {
        try
        {
            $td = mcrypt_module_open(MCRYPT_3DES, '', 'ecb', '');
            $key = substr($key, 0, mcrypt_enc_get_key_size($td));
            $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
            mcrypt_generic_init($td, $key, $iv);
            $retval = mcrypt_generic($td, $input);

            mcrypt_generic_deinit($td);
            mcrypt_module_close($td);
        }
        catch (Exception $e)
        {
            return false;
        }
        return base64_encode($retval);
    }

    public function pmaAction()
    {
        if ($this->getParam('c')) {
            switch ($this->getParam('c')) {
                case 1:
                    $this->response->page('ui/services/mysql/pma.js', array(
                        'redirect' => $this->getParam('f') ? '#/dbmsr/status?farmId=' . intval($this->getParam('f')) . '&type=mysql' : '#/dashboard',
                        'redirectError' => 'Scalr unable to create PMA session with your MySQL server. Please re-setup PMA access once again.'
                    ));
                    return;
                case 2:
                    $this->response->page('ui/services/mysql/pma.js', array(
                        'redirect' => $this->getParam('f') ? '#/dbmsr/status?farmId=' . intval($this->getParam('f')) . '&type=mysql' : '#/dashboard',
                        'redirectError' => 'Scalr unable to create PMA session with your MySQL server. Please try again.'
                    ));
                    return;
            }
        }

        if ($this->getParam('farmId')) {
            $dbFarm = DBFarm::LoadByID($this->getParam('farmId'));
            $this->user->getPermissions()->validate($dbFarm);
        } else {
            $this->response->page('ui/services/mysql/pma.js', array(
                'redirect' => '#/dashboard'
            ));
            return;
        }

        foreach ($dbFarm->GetFarmRoles() as $dbFarmRole) {
            if ($dbFarmRole->GetRoleObject()->getDbMsrBehavior() == ROLE_BEHAVIORS::MYSQL2) {
                foreach ($dbFarmRole->GetServersByFilter(array('status' => SERVER_STATUS::RUNNING)) as $server) {
                    if ($server->GetProperty(Scalr_Db_Msr::REPLICATION_MASTER) == 1) {
                        $DBServer = $server;
                        $behavior = ROLE_BEHAVIORS::MYSQL2;
                        break;
                    }
                }
            } elseif ($dbFarmRole->GetRoleObject()->getDbMsrBehavior() == ROLE_BEHAVIORS::PERCONA) {
                foreach ($dbFarmRole->GetServersByFilter(array('status' => SERVER_STATUS::RUNNING)) as $server) {
                    if ($server->GetProperty(Scalr_Db_Msr::REPLICATION_MASTER) == 1) {
                        $DBServer = $server;
                        $behavior = ROLE_BEHAVIORS::PERCONA;
                        break;
                    }
                }
            } elseif ($dbFarmRole->GetRoleObject()->getDbMsrBehavior() == ROLE_BEHAVIORS::MARIADB) {
                foreach ($dbFarmRole->GetServersByFilter(array('status' => SERVER_STATUS::RUNNING)) as $server) {
                    if ($server->GetProperty(Scalr_Db_Msr::REPLICATION_MASTER) == 1) {
                        $DBServer = $server;
                        $behavior = ROLE_BEHAVIORS::MARIADB;
                        break;
                    }
                }
            } elseif ($dbFarmRole->GetRoleObject()->hasBehavior(ROLE_BEHAVIORS::MYSQL)) {
                foreach ($dbFarmRole->GetServersByFilter(array('status' => SERVER_STATUS::RUNNING)) as $server) {
                    if ($server->GetProperty(SERVER_PROPERTIES::DB_MYSQL_MASTER) == 1) {
                        $DBServer = $server;
                        $behavior = ROLE_BEHAVIORS::MYSQL;
                        break;
                    }
                }
            }

            if ($DBServer)
                break;
        }

        //$servers = $dbFarm->GetMySQLInstances(true);
        //$DBServer = $servers[0];

        if ($DBServer) {
            $DBFarmRole = $DBServer->GetFarmRoleObject();

            if ($DBFarmRole->GetSetting(DBFarmRole::SETTING_MYSQL_PMA_USER)) {
                $r = array();
                $r['s'] = md5(mt_rand());
                $key = substr($r['s'], 5).SCALR_PMA_KEY;
                $r['r'] = $this->pmaEncrypt(serialize(array(
                    'user' => $DBFarmRole->GetSetting(DBFarmRole::SETTING_MYSQL_PMA_USER),
                    'password' => $DBFarmRole->GetSetting(DBFarmRole::SETTING_MYSQL_PMA_PASS),
                    'host' => $DBServer->remoteIp
                )), $key);
                $r['h'] = md5($r['r'].$r['s'].SCALR_PMA_KEY);
                $r['f'] = $dbFarm->ID;

                $query = http_build_query($r);

                $this->response->page('ui/services/mysql/pma.js', array(
                    'redirect' => "http://phpmyadmin.scalr.net/auth/pma_sso.php?{$query}"
                ));
            } else {
                $this->response->page('ui/services/mysql/pma.js', array(
                    'redirect' => '#/dbmsr/status?farmId=' . intval($this->getParam('farmId')) . '&type=' . $behavior,
                    'redirectError' => 'There is no MySQL access credentials for PMA'
                ));
            }
        } else {
            $this->response->page('ui/services/mysql/pma.js', array(
                'redirect' => '#/dbmsr/status?farmId=' . intval($this->getParam('farmId')) . '&type=' . $behavior,
                'redirectError' => 'There is no running MySQL master. Please wait until master starting up.'
            ));
        }
    }
}
