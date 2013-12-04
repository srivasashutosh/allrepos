<?php
class Scalr_UI_Controller_Services_Ssl_Certificates extends Scalr_UI_Controller
{
    const CALL_PARAM_NAME = 'certId';

    public function defaultAction()
    {
        $this->viewAction();
    }

    public function viewAction()
    {
        $this->response->page('ui/services/ssl/certificates/view.js');
    }

    public function getList()
    {
        return $this->db->GetAll('SELECT id, name FROM services_ssl_certs WHERE env_id = ?', array($this->getEnvironmentId()));
    }

    public function xRemoveAction()
    {
        $this->request->defineParams(array(
            'certs' => array('type' => 'json')
        ));

        foreach ($this->getParam('certs') as $certId) {
            $cert = new Scalr_Service_Ssl_Certificate();
            $cert->loadById($certId);
            $this->user->getPermissions()->validate($cert);

            // TODO: used by ?

            $cert->delete();
        }

        $this->response->success();
    }

    public function editAction()
    {
        $cert = new Scalr_Service_Ssl_Certificate();
        $cert->loadById($this->getParam('certId'));
        $this->user->getPermissions()->validate($cert);

        $this->response->page('ui/services/ssl/certificates/create.js', array(
            'cert' => array(
                'id' => $cert->id,
                'name' => $cert->name,
                'sslPkey' => $cert->sslPkey ? 'Private key uploaded' : '',
                'sslCert' => $cert->sslCert ? $cert->getSslCertName() : '',
                'sslCabundle' => $cert->sslCabundle ? $cert->getSslCabundleName() : ''
            )
        ));
    }

    public function createAction()
    {
        $this->response->page('ui/services/ssl/certificates/create.js');
    }

    public function xSaveAction()
    {
        $cert = new Scalr_Service_Ssl_Certificate();
        $flagNew = false;
        if ($this->getParam('id')) {
            $cert->loadById($this->getParam('id'));
            $this->user->getPermissions()->validate($cert);
        } else {
            $cert->envId = $this->getEnvironmentId();
            $flagNew = true;
        }

        if (! $this->getParam('name'))
            throw new Scalr_Exception_Core('Name can\'t be empty');

        $cert->name = htmlspecialchars($this->getParam('name'));

        if ($_FILES['sslPkey']['tmp_name'])
            $cert->sslPkey = file_get_contents($_FILES['sslPkey']['tmp_name']);

        if ($_FILES['sslCert']['tmp_name'])
            $cert->sslCert = file_get_contents($_FILES['sslCert']['tmp_name']);

        if ($_FILES['sslCabundle']['tmp_name'])
            $cert->sslCabundle = file_get_contents($_FILES['sslCabundle']['tmp_name']);

        $cert->save();
        $this->response->success('Certificate was successfully added');
        if ($flagNew)
            $this->response->data(array('cert' => array(
                'id' => (string)$cert->id,
                'name' => $cert->name
            )));
    }

    public function xListCertificatesAction()
    {
        $this->request->defineParams(array(
            'sort' => array('type' => 'json')
        ));

        $sql = "SELECT id, name, ssl_pkey AS sslPkey, ssl_cert AS sslCert, ssl_cabundle AS sslCabundle FROM `services_ssl_certs` WHERE env_id = ? AND :FILTER:";
        $response = $this->buildResponseFromSql2($sql, array('id', 'name'), array('id', 'name'), array($this->getEnvironmentId()));

        foreach ($response['data'] as &$row) {
            $row['sslPkey'] = !!$row['sslPkey'];

            if ($row['sslCert']) {
                $info = openssl_x509_parse($row['sslCert'], false);
                $row['sslCert'] = $info['name'] ? $info['name'] : 'uploaded';
            }

            if ($row['sslCabundle']) {
                $info = openssl_x509_parse($row['sslCabundle'], false);
                $row['sslCabundle'] = $info['name'] ? $info['name'] : 'uploaded';
            }
        }

        $this->response->data($response);
    }
}
