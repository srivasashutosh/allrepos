<?php
require(dirname(__FILE__)."/../../src/prepend.inc.php");

$setupInfo = $db->GetRow("SELECT * FROM upd.scalr_setups WHERE scalr_id = ?", array($_GET['scalr_id']));
if (!$setupInfo)
    die("Unrecognized setup: update denied. Please email contents of '/path_to_scalr/etc/id' to update_server@scalr.com to be whitelisted.");

$rs20 = $db->Execute("SELECT * FROM roles WHERE env_id = '0' AND client_id = '0' AND generation='2'");
$result = array();
while ($role = $rs20->FetchRow()) {

    $role['role_tags'] = $db->GetAll("SELECT * FROM role_tags WHERE role_id = ?", array($role['id']));
    $role['role_software'] = $db->GetAll("SELECT * FROM role_software WHERE role_id = ?", array($role['id']));
    $role['role_security_rules'] = $db->GetAll("SELECT * FROM role_security_rules WHERE role_id = ?", array($role['id']));
    $role['role_properties'] = $db->GetAll("SELECT * FROM role_properties WHERE role_id = ?", array($role['id']));
    $role['role_parameters'] = $db->GetAll("SELECT * FROM role_parameters WHERE role_id = ?", array($role['id']));
    $role['role_images'] = $db->GetAll("SELECT * FROM role_images WHERE role_id = ?", array($role['id']));
    $role['role_behaviors'] = $db->GetAll("SELECT * FROM role_behaviors WHERE role_id = ?", array($role['id']));

    $isOldMySQL = $db->GetOne("SELECT id FROM role_behaviors WHERE role_id = ? AND behavior='mysql'", array($role['id']));

    if (!$isOldMySQL)
        $result[] = $role;
}

print json_encode($result);
exit();