<?php
class Scalr_UI_Controller_Services_Apache_Vhosts extends Scalr_UI_Controller
{
    const CALL_PARAM_NAME = 'vhostId';
    protected $farmWidgetOptions = array('behavior_app', 'disabledServer', 'requiredFarm', 'requiredFarmRole');

    public static function getPermissionDefinitions()
    {
        return array();
    }

    public function defaultAction()
    {
        $this->viewAction();
    }

    public function viewAction()
    {
        $this->response->page('ui/services/apache/vhosts/view.js');
    }

    public function xRemoveAction()
    {
        $this->request->defineParams(array(
            'vhosts' => array('type' => 'json')
        ));

        foreach ($this->getParam('vhosts') as $vhostId) {
            $info = $this->db->GetRow("SELECT id, farm_id FROM apache_vhosts WHERE id = ? AND env_id = ?",
                array($vhostId, $this->getEnvironmentId())
            );

            if ($info['id']) {
                $this->db->Execute("DELETE FROM apache_vhosts WHERE id = ? AND env_id = ?",
                    array($vhostId, $this->getEnvironmentId())
                );

                if ($info['farm_id']) {
                    $dbFarm = DBFarm::LoadByID($info['farm_id']);

                    $servers = $dbFarm->GetServersByFilter(array('status' => array(SERVER_STATUS::INIT, SERVER_STATUS::RUNNING)));
                    foreach ($servers as $dBServer) {
                        if ($dBServer->GetFarmRoleObject()->GetRoleObject()->hasBehavior(ROLE_BEHAVIORS::NGINX) ||
                            $dBServer->GetFarmRoleObject()->GetRoleObject()->hasBehavior(ROLE_BEHAVIORS::APACHE))
                            $dBServer->SendMessage(new Scalr_Messaging_Msg_VhostReconfigure());
                    }
                }
            }
        }

        $this->response->success();
    }

    public function editAction()
    {
        $params = array(
            'sslCertificates' => self::loadController('Certificates', 'Scalr_UI_Controller_Services_Ssl')->getList()
        );

        if ($this->getParam('vhostId')) {
            $vHost = Scalr_Service_Apache_Vhost::init()->loadById($this->getParam('vhostId'));
            $this->user->getPermissions()->validate($vHost);
            $options = unserialize($vHost->templateOptions);

            $params['farmWidget'] = self::loadController('Farms')->getFarmWidget(array('farmId' => $vHost->farmId ? $vHost->farmId : "", 'farmRoleId' => $vHost->farmRoleId ? $vHost->farmRoleId : ""), $this->farmWidgetOptions);
            $params['vhost'] = array(
                'vhostId' => $vHost->id,
                'domainName' => $vHost->domainName,
                'documentRoot' => $options['document_root'],
                'logsDir' => $options['logs_dir'],
                'serverAdmin' => $options['server_admin'],
                'serverAlias' => $options['server_alias'],
                'nonSslTemplate' => $vHost->httpdConf,
                'isSslEnabled' => $vHost->isSslEnabled == 1 ? true : false,
                'sslCertId' => $vHost->sslCertId,
                'sslTemplate' => $vHost->httpdConfSsl ? $vHost->httpdConfSsl : @file_get_contents("../templates/services/apache/ssl.vhost.tpl")
            );
        } else {
            $params['farmWidget'] = self::loadController('Farms')->getFarmWidget(array(), $this->farmWidgetOptions);
            $params['vhost'] = array(
                'documentRoot' => '/var/www',
                'logsDir' => '/var/log',
                'serverAdmin' => $this->user->getEmail(),
                'nonSslTemplate' => @file_get_contents("../templates/services/apache/nonssl.vhost.tpl"),
                'sslTemplate' => @file_get_contents("../templates/services/apache/ssl.vhost.tpl")
            );
        }

        $this->response->page('ui/services/apache/vhosts/create.js', $params);
    }

    public function createAction()
    {
        $this->editAction();
    }

    public function xSaveAction()
    {
        $validator = new Scalr_Validator();

        try {
            if ($validator->validateDomain($this->getParam('domainName')) !== true)
                $err['domainName'] = _("Domain name is incorrect");

            if (!$this->getParam('farmId'))
                $err['farmId'] = _("Farm required");
            else {
                $dbFarm = DBFarm::LoadByID($this->getParam('farmId'));
                $this->user->getPermissions()->validate($dbFarm);
            }

            if (!$this->getParam('farmRoleId'))
                $err['farmRoleId'] = _("Role required");
            else {
                $dbFarmRole = DBFarmRole::LoadByID($this->getParam('farmRoleId'));

                if($dbFarmRole->FarmID != $dbFarm->ID)
                    $err['farmRoleId'] = _("Role not found");
            }

            if($validator->validateEmail($this->getParam('serverAdmin'), null, true) !== true)
                $err['serverAdmin'] = _("Server admin's email is incorrect or empty ");

            if(!$this->getParam('documentRoot'))
                $err['documentRoot'] = _("Document root required");

            if(!$this->getParam('logsDir'))
                $err['logsDir'] = _("Logs directory required");

            if ($this->db->GetOne("SELECT id FROM apache_vhosts WHERE env_id=? AND `name` = ? AND id != ? AND farm_id = ?",
                array($this->getEnvironmentId(), $this->getParam('domainName'), $this->getParam('vhostId'), $this->getParam('farmId')))
            )
                $err['domainName'] = "'{$this->getParam('domainName')}' virtualhost already exists";

        } catch (Exception $e) {
            $err[] = $e->getMessage();
        }

        if (count($err) == 0) {
            $vHost = Scalr_Service_Apache_Vhost::init();
            if ($this->getParam('vhostId')) {
                $vHost->loadById($this->getParam('vhostId'));
                $this->user->getPermissions()->validate($vHost);
            } else {
                $vHost->envId = $this->getEnvironmentId();
                $vHost->clientId = $this->user->getAccountId();
            }

            $vHost->domainName = $this->getParam('domainName');
            $isSslEnabled = $this->getParam('isSslEnabled') == 'on' ? true : false;
            $vHost->farmId = $this->getParam('farmId');
            $vHost->farmRoleId = $this->getParam('farmRoleId');
            $vHost->isSslEnabled = $isSslEnabled ? 1 : 0;

            $vHost->httpdConf = $this->getParam("nonSslTemplate");

            $vHost->templateOptions = serialize(array(
                "document_root" 	=> trim($this->getParam('documentRoot')),
                "logs_dir"			=> trim($this->getParam('logsDir')),
                "server_admin"		=> trim($this->getParam('serverAdmin')),
                "server_alias"		=> trim($this->getParam('serverAlias'))
            ));

            //SSL stuff
            if ($isSslEnabled) {
                $cert = new Scalr_Service_Ssl_Certificate();
                $cert->loadById($this->getParam('sslCertId'));
                $this->user->getPermissions()->validate($cert);

                $vHost->sslCertId = $cert->id;
                $vHost->httpdConfSsl = $this->getParam("sslTemplate");
            } else {
                $vHost->sslCertId = 0;
                $vHost->httpdConfSsl = "";
            }

            $vHost->save();

            $servers = $dbFarm->GetServersByFilter(array('status' => array(SERVER_STATUS::INIT, SERVER_STATUS::RUNNING)));
            foreach ($servers as $dBServer) {
                if ($dBServer->GetFarmRoleObject()->GetRoleObject()->hasBehavior(ROLE_BEHAVIORS::NGINX) ||
                    ($dBServer->GetFarmRoleObject()->GetRoleObject()->hasBehavior(ROLE_BEHAVIORS::APACHE) && $dBServer->farmRoleId == $vHost->farmRoleId)) {
                        $dBServer->SendMessage(new Scalr_Messaging_Msg_VhostReconfigure());
                    }
            }

            $this->response->success(_('Virtualhost successfully saved'));
        } else {
            $this->response->failure();
            $this->response->data(array('errors' => $err));
        }
    }

    public function xListVhostsAction()
    {
        $this->request->defineParams(array(
            'farmRoleId' => array('type' => 'int'),
            'farmId' => array('type' => 'int'),
            'vhostId' => array('type' => 'int'),
            'sort' => array('type' => 'json')
        ));

        $sql = 'SELECT * FROM `apache_vhosts` WHERE env_id = ? AND :FILTER:';
        $args = array($this->getEnvironmentId());

        if ($this->getParam('farmId')) {
            $sql .= ' AND farm_id = ?';
            $args[] = $this->getParam('farmId');
        }

        if ($this->getParam('farmRoleId')) {
            $sql .= ' AND farm_roleid = ?';
            $args[] = $this->getParam('farmRoleId');
        }

        if ($this->getParam('vhostId')) {
            $sql .= ' AND id = ?';
            $args[] = $this->getParam('vhostId');
        }

        $response = $this->buildResponseFromSql2($sql, array('id', 'name', 'farm_id', 'farm_roleid', 'last_modified', 'is_ssl_enabled'), array('name'), $args);

        foreach ($response['data'] as &$row) {
            $row['last_modified'] = Scalr_Util_DateTime::convertTz($row['last_modified']);
            if ($row['farm_roleid'])
            {
                try {
                    $DBFarmRole = DBFarmRole::LoadByID($row['farm_roleid']);

                    $row['farm_name'] 		= $DBFarmRole->GetFarmObject()->Name;
                    $row['role_name'] 		= $DBFarmRole->GetRoleObject()->name;

                } catch(Exception $e)
                {
                    if (stristr($e->getMessage(), "not found"))
                        $this->db->Execute ("DELETE FROM apache_vhosts WHERE id=?", array($row['id']));
                }
            }
        }

        $this->response->data($response);
    }
}
