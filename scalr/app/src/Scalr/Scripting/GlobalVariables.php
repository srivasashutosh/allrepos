<?php

class Scalr_Scripting_GlobalVariables
{
    const SCOPE_ENVIRONMENT = 'env';
    const SCOPE_ROLE = 'role';
    const SCOPE_FARM = 'farm';
    const SCOPE_FARMROLE = 'farmrole';

    private $envId,
        $scope,
        $db,
        $crypto,
        $cryptoKey;

    public function __construct($envId, $scope = Scalr_Scripting_GlobalVariables::SCOPE_ENVIRONMENT)
    {
        $this->crypto = new Scalr_Util_CryptoTool(
            MCRYPT_RIJNDAEL_256,
            MCRYPT_MODE_CFB,
            @mcrypt_get_key_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CFB),
            @mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CFB)
        );

        $this->cryptoKey = @file_get_contents(APPPATH."/etc/.cryptokey");

        $this->envId = $envId;
        $this->scope = $scope;
        $this->db = \Scalr::getDb();
    }

    public function setValues($variables, $roleId = 0, $farmId = 0, $farmRoleId = 0)
    {
        // info about variable from upper levels, make copy of arguments for query
        $valFarmId = $farmId;
        $valRoleId = $roleId;
        $valFarmRoleId = $farmRoleId;
        $valSkip = false;
        if ($this->scope == self::SCOPE_FARMROLE) {
            $valFarmRoleId = 0;
        } else if ($this->scope == self::SCOPE_FARM) {
            $valFarmId = 0;
        } else if ($this->scope == self::SCOPE_ROLE) {
            $valRoleId = 0;
        } else {
            $valSkip = true;
        }

        // check for required variables
        if (! $valSkip) {
            $values = $this->db->GetAll("SELECT * FROM global_variables
                WHERE flag_required = 1 AND env_id = ? AND (role_id = ? or role_id = 0) AND (farm_id = ? OR farm_id = 0) AND (farm_role_id = ? OR farm_role_id = 0)", array(
                $this->envId,
                $valRoleId,
                $valFarmId,
                $valFarmRoleId
            ));

            $vars = array();
            foreach ($values as $v) {
                $vars[$v['name']] = $vars[$v['name']] || !empty($v['value']);
            }

            $required = array();
            foreach ($vars as $name => $flag) {
                if ($flag)
                    continue;

                $founded = false;
                foreach ($variables as $variable) {
                    if ($name == $variable['name']) {
                        if (empty($variable['value']))
                            $required[] = $variable['name'];
                        $founded = true;
                    }
                }

                if (! $founded)
                    $required[] = $name;
            }

            if (count($required)) {
                throw new Scalr_Exception_Core(sprintf('%s are required variables at scope %s', implode(', ', $required), $this->scope));
            }
        }

        if (!empty($variables)) {
            foreach ($variables as $variable) {
                if (empty($variable['name']))
                    continue;

                $variable['value'] = trim($variable['value']);

                if (! preg_match('/^[A-Za-z]{1,1}[A-Za-z0-9_]{1,49}$/', $variable['name'])) {
                    throw new Scalr_Exception_Core(sprintf('"%s" is invalid name for variable at scope %s', $variable['name'], $this->scope));
                }

                if ($variable['scope'] != $this->scope)
                    continue;

                if ($variable['flagDelete']) {
                    $this->db->Execute("DELETE FROM `global_variables` WHERE name = ? AND env_id = ? AND role_id = ? AND farm_id = ? AND farm_role_id = ?", array(
                        $variable['name'],
                        $this->envId,
                        $roleId,
                        $farmId,
                        $farmRoleId
                    ));
                    continue;
                }

                if (! $valSkip) {
                    $values = $this->db->GetAll("SELECT * FROM global_variables
                    WHERE name = ? AND flag_final = 1 AND env_id = ? AND (role_id = ? or role_id = 0) AND (farm_id = ? OR farm_id = 0) AND (farm_role_id = ? OR farm_role_id = 0)", array(
                        $variable['name'],
                        $this->envId,
                        $valRoleId,
                        $valFarmId,
                        $valFarmRoleId
                    ));

                    if (count($values)) {
                        throw new Scalr_Exception_Core(sprintf('You can\'t change final variable "%s" at scope %s', $variable['name'], $this->scope));
                    }
                }

                if ($variable['value'])
                    $variable['value'] = $this->crypto->encrypt($variable['value'], $this->cryptoKey);

                $this->db->Execute("INSERT INTO `global_variables` SET
                    `env_id` = ?,
                    `role_id` = ?,
                    `farm_id` = ?,
                    `farm_role_id` = ?,
                    `name` = ?,
                    `value` = ?,
                    `flag_final` = ?,
                    `flag_required` = ?,
                    `scope` = ?
                    ON DUPLICATE KEY UPDATE `value` = ?, `flag_final` = ?, `flag_required` = ?", array(
                    $this->envId,
                    $roleId,
                    $farmId,
                    $farmRoleId,
                    $variable['name'],
                    $variable['value'],
                    $variable['flagFinal'],
                    $variable['flagRequired'],
                    $variable['scope'],

                    $variable['value'],
                    $variable['flagFinal'],
                    $variable['flagRequired']
                ));
            }
        }
    }

    public function getValues($roleId = 0, $farmId = 0, $farmRoleId = 0)
    {
        $vars = $this->db->GetAll("SELECT name, value, scope, flag_final AS flagFinal, flag_required AS flagRequired FROM `global_variables` WHERE env_id = ?
            AND (role_id = '0' OR role_id = ?)
            AND (farm_id = '0' OR farm_id = ?)
            AND (farm_role_id = '0' OR farm_role_id = ?)
            ", array(
            $this->envId,
            $roleId,
            $farmId,
            $farmRoleId
        ));

        $groupByName = array();
        foreach ($vars as $value) {
            $groupByName[$value['name']][$value['scope']] = $value;
        }

        $result = array();
        foreach ($groupByName as $name => $value) {
            if ($value[$this->scope])
                $current = $value[$this->scope];
            else
                $current = array('name' => $name);

            if ($current['value'])
                $current['value'] = $this->crypto->decrypt($current['value'], $this->cryptoKey);

            $order = array(self::SCOPE_FARMROLE, self::SCOPE_FARM, self::SCOPE_ROLE, self::SCOPE_ENVIRONMENT);
            $index = array_search($this->scope, $order);

            if ($index)
                $order = array_slice($order, $index + 1);

            foreach ($order as $scope) {
                if ($value[$scope]) {
                    if (!$current['scope'])
                        $current['scope'] = $value[$scope]['scope'];

                    if (!$current['defaultValue'] || $current['defaultScope'] == $this->scope) {
                        // if we have other scope value, replace defaultValue with it (only once)
                        $current['defaultValue'] = $this->crypto->decrypt($value[$scope]['value'], $this->cryptoKey);
                        $current['defaultScope'] = $scope;
                    }

                    if ($value[$scope]['flagRequired'] == 1)
                        $current['flagRequiredGlobal'] = 1;

                    if ($value[$scope]['flagFinal'] == 1)
                        $current['flagFinalGlobal'] = 1;
                }
            }

            $result[] = $current;
        }

        return $result;
    }

    public function listVariables($roleId = 0, $farmId = 0, $farmRoleId = 0)
    {
        $envVars = $this->db->GetAll("SELECT name, value FROM global_variables WHERE env_id = ? AND role_id = '0' AND farm_id = '0' AND farm_role_id = '0'", array($this->envId));

        if ($roleId)
            $roleVars = $this->db->GetAll("SELECT name, value FROM global_variables WHERE env_id = ? AND role_id = ? AND farm_id = '0' AND farm_role_id = '0'", array($this->envId, $roleId));

        if ($farmId)
            $farmVars = $this->db->GetAll("SELECT name, value FROM global_variables WHERE env_id = ? AND role_id = '0' AND farm_id = ? AND farm_role_id = '0'", array($this->envId, $farmId));

        if ($farmRoleId)
            $farmRoleVars = $this->db->GetAll("SELECT name, value FROM global_variables WHERE env_id = ? AND farm_role_id = ?", array($this->envId, $farmRoleId));

        $retval = array();
        foreach ($envVars as $var)
            $retval[$var['name']] = trim($this->crypto->decrypt($var['value'], $this->cryptoKey));

        if ($roleVars)
            foreach ($roleVars as $var)
                $retval[$var['name']] = trim($this->crypto->decrypt($var['value'], $this->cryptoKey));

        if ($farmVars)
            foreach ($farmVars as $var)
                $retval[$var['name']] = trim($this->crypto->decrypt($var['value'], $this->cryptoKey));

        if ($farmRoleVars)
            foreach ($farmRoleVars as $var)
                $retval[$var['name']] = trim($this->crypto->decrypt($var['value'], $this->cryptoKey));

        return $retval;
    }
}
