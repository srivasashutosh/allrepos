#!/usr/bin/env php
<?php

// Migration to new config script

define("NO_TEMPLATES", 1);

require_once __DIR__ . '/../src/prepend.inc.php';

set_time_limit(0);

$ScalrUpdate = new Update20130605();
$ScalrUpdate->Run();

class Update20130605
{

    public function Run()
    {
        $container = Scalr::getContainer();
        $db = $container->adodb;

        $this->crypto = new Scalr_Util_CryptoTool(
            MCRYPT_RIJNDAEL_256,
            MCRYPT_MODE_CFB,
            // This doesn't work for mcrypt 2.4.x or 2.5.x. changing to new behavior corrupt previously encoded strings
            // http://us3.php.net/mcrypt_get_key_size
            // TODO: use different interfaces depend on mcrypt library. or hardcode keys size
            @mcrypt_get_key_size(MCRYPT_RIJNDAEL_256),
            @mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CFB)
        );

        $this->crypto2 = new Scalr_Util_CryptoTool(
            MCRYPT_RIJNDAEL_256,
            MCRYPT_MODE_CFB,
            @mcrypt_get_key_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CFB),
            @mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CFB)
        );

        $this->cryptoKey = @file_get_contents(APPPATH."/etc/.cryptokey");

        $vars = $db->Execute("SELECT * FROM global_variables");
        while ($var = $vars->FetchRow()) {
            if ($var['value']) {
                $value = $this->crypto->decrypt($var['value'], $this->cryptoKey);

                $db->Execute("UPDATE global_variables SET value = ? WHERE env_id = ? AND farm_id = ? AND farm_role_id = ? AND name = ? AND scope = ?", array(
                    $this->crypto2->encrypt($value, $this->cryptoKey),
                    $var['env_id'],
                    $var['farm_id'],
                    $var['farm_role_id'],
                    $var['name'],
                    $var['scope']
                ));
            }
        }
    }
}