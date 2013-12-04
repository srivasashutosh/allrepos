<?php
class Scalr_UI_Controller_Sshkeys extends Scalr_UI_Controller
{
    const CALL_PARAM_NAME = 'sshKeyId';

    public static function getPermissionDefinitions()
    {
        return array();
    }

    public function viewAction()
    {
        $farms = self::loadController('Farms')->getList();
        array_unshift($farms, array('id' => 0, 'name' => 'All farms'));

        $this->response->page('ui/sshkeys/view.js', array('farms' => $farms));
    }

    public function downloadPrivateAction()
    {
        $this->request->defineParams(array(
            'sshKeyId' => array('type' => 'int')
        ));

        $sshKey = Scalr_SshKey::init()->loadById($this->getParam('sshKeyId'));
        $this->user->getPermissions()->validate($sshKey);

        $retval = $sshKey->getPrivate();

        if ($sshKey->cloudLocation)
            $fileName = "{$sshKey->cloudKeyName}.{$sshKey->cloudLocation}.private.pem";
        else
            $fileName = "{$sshKey->cloudKeyName}.private.pem";

        $this->response->setHeader('Pragma', 'private');
        $this->response->setHeader('Cache-control', 'private, must-revalidate');
        $this->response->setHeader('Content-type', 'plain/text');
        $this->response->setHeader('Content-Disposition', 'attachment; filename="'.$fileName.'"');
        $this->response->setHeader('Content-Length', strlen($retval));

        $this->response->setResponse($retval);
    }

    public function downloadPublicAction()
    {
        $this->request->defineParams(array(
            'sshKeyId' => array('type' => 'int')
        ));

        $sshKey = Scalr_SshKey::init()->loadById($this->getParam('sshKeyId'));
        $this->user->getPermissions()->validate($sshKey);

        $retval = $sshKey->getPublic();
        if (!$retval)
            $retval = $sshKey->generatePublicKey();

        if ($sshKey->cloudLocation)
            $fileName = "{$sshKey->cloudKeyName}.{$sshKey->cloudLocation}.public.pem";
        else
            $fileName = "{$sshKey->cloudKeyName}.public.pem";

        $this->response->setHeader('Pragma', 'private');
        $this->response->setHeader('Cache-control', 'private, must-revalidate');
        $this->response->setHeader('Content-type', 'plain/text');
        $this->response->setHeader('Content-Disposition', 'attachment; filename="'.$fileName.'"');
        $this->response->setHeader('Content-Length', strlen($retval));

        $this->response->setResponse($retval);
    }

    public function deleteAction()
    {
        $this->request->defineParams(array(
            'sshKeyId' => array('type' => 'int')
        ));

        $sshKey = Scalr_SshKey::init()->loadById($this->getParam('sshKeyId'));
        $this->user->getPermissions()->validate($sshKey);

        if ($sshKey->type == Scalr_SshKey::TYPE_GLOBAL) {
            if ($sshKey->platform == 'ec2') {
                $aws = $this->getEnvironment()->aws($sshKey->cloudLocation);
                $aws->ec2->keyPair->delete($sshKey->cloudKeyName);
                $sshKey->delete();

                $this->response->success();
            } else {
                $sshKey->delete();
            }
        } else {
            //TODO:
        }

        $this->response->success("SSH key successfully removed");
    }

    public function regenerateAction()
    {
        $env = $this->getEnvironment();
        $this->request->defineParams(array(
            'sshKeyId' => array('type' => 'int')
        ));

        $sshKey = Scalr_SshKey::init()->loadById($this->getParam('sshKeyId'));
        $this->user->getPermissions()->validate($sshKey);

        if ($sshKey->type == Scalr_SshKey::TYPE_GLOBAL) {
            if ($sshKey->platform == 'ec2') {
                $aws = $env->aws($sshKey->cloudLocation);
                $aws->ec2->keyPair->delete($sshKey->cloudKeyName);
                $result = $aws->ec2->keyPair->create($sshKey->cloudKeyName);

                if (!empty($result->keyMaterial)) {
                    $sshKey->setPrivate($result->keyMaterial);
                    $pubKey = $sshKey->generatePublicKey();
                    if (!$pubKey) {
                        throw new Exception("Keypair generation failed");
                    }
                    $oldKey = $sshKey->getPublic();

                    $sshKey->setPublic($pubKey);
                    $sshKey->save();

                    $dbFarm = DBFarm::LoadByID($sshKey->farmId);
                    $servers = $dbFarm->GetServersByFilter(array('platform' => SERVER_PLATFORMS::EC2, 'status' => array(SERVER_STATUS::RUNNING, SERVER_STATUS::INIT, SERVER_STATUS::PENDING)));
                    foreach ($servers as $dbServer) {
                        if ($dbServer->GetCloudLocation() == $sshKey->cloudLocation) {
                            $msg = new Scalr_Messaging_Msg_UpdateSshAuthorizedKeys(array($pubKey), array($oldKey));
                            $dbServer->SendMessage($msg);
                        }
                    }

                    $this->response->success();
                }
            } else {
                //TODO: regenerate ssh key for the different platforms
            }
        } else {
            //TODO:
        }
    }

    /**
     * Get list of roles for listView
     */
    public function xListSshKeysAction()
    {
        $this->request->defineParams(array(
            'sshKeyId' => array('type' => 'int'),
            'farmId'   => array('type' => 'int'),
            'sort'     => array('type' => 'json')
        ));

        $sql = 'SELECT id FROM ssh_keys WHERE env_id = ? AND :FILTER:';
        $params = array($this->getEnvironmentId());

        if ($this->getParam('sshKeyId')) {
            $sql .= " AND id = ?";
            $params[] = $this->getParam('sshKeyId');
        }

        if ($this->getParam('farmId')) {
            $sql .= " AND farm_id = ?";
            $params[] = $this->getParam('farmId');
        }

        $response = $this->buildResponseFromSql(
            $sql,
            array('id', 'type', 'cloud_location'),
            array('cloud_key_name', 'cloud_location', 'farm_id', 'id'),
            $params
        );

        foreach ($response["data"] as &$row) {
            $sshKey = Scalr_SshKey::init()->loadById($row['id']);

            $row = array(
                'id'				=> $sshKey->id,
                'type'				=> ($sshKey->type == Scalr_SshKey::TYPE_GLOBAL) ? "{$sshKey->type} ({$sshKey->platform})" : $sshKey->type,
                'cloud_key_name'	=> $sshKey->cloudKeyName,
                'farm_id'		    => $sshKey->farmId,
                'cloud_location'    => $sshKey->cloudLocation
            );
        }

        $this->response->data($response);
    }
}
