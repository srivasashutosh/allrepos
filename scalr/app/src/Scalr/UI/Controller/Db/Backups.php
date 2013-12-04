<?php
class Scalr_UI_Controller_Db_Backups extends Scalr_UI_Controller
{
    public function defaultAction()
    {
        $farms = self::loadController('Farms')->getList();
        array_unshift($farms, array('id' => 0, 'name' => 'All farms'));

        $data = $this->getBackupsList();
        $this->response->page('ui/db/backups/view.js', array(
                'farms' => $farms,
                'backups' => $data,
                'env' => $this->user->getEnvironments()
            ),
            array('ui/db/backups/calendarviews.js'),
            array('ui/db/backups/view.css')
        );
    }

    public function detailsAction()
    {
        $this->response->page('ui/db/backups/details.js',
            array(
                'backup' => $this->getBackupDetails($this->getParam('backupId'))
            ), array(), array( 'ui/db/backups/view.css')
        );
    }

    public function xGetListBackupsAction()
    {
        $this->response->data(array('backups' => $this->getBackupsList($this->getParam('time'))));
    }

    private function getBackupsList($time = '')
    {
        $data = array();
        $farmNames = array();
        $time = ($time == '') ? time() : strtotime($time);

        $sql = "SELECT id as backup_id, farm_id, service as role, dtcreated as date FROM services_db_backups WHERE status = ? AND env_id = ? AND DATE_FORMAT(CONVERT_TZ(dtcreated, 'SYSTEM', ?), '%Y-%m') = ?";
        $args = array(Scalr_Db_Backup::STATUS_AVAILABLE, $this->getEnvironmentId(), $this->user->getSetting(Scalr_Account_User::SETTING_UI_TIMEZONE), date('Y-m', $time));

        if ($this->getParam('farmId')) {
            $sql .= ' AND farm_id = ?';
            $args[] = $this->getParam('farmId');
        }

        $dbBackupResult = $this->buildResponseFromSql2($sql, array(), array(), $args, true);
        foreach ($dbBackupResult['data'] as $row) {
            $date = strtotime(Scalr_Util_DateTime::convertTz($row['date']));
            $row['date'] = date('h:ia ', $date);
            if (! $farmNames[$row['farm_id']])
                $farmNames[$row['farm_id']] = DBFarm::LoadByIDOnlyName($row['farm_id']);

            $row['farm'] = $farmNames[$row['farm_id']];
            $data[date('n Y', $date)][date('j F o', $date)][date('H:i', $date)] = $row;
        }

        return $data;
    }

    private function getBackupDetails($backupId)
    {
        $links = array();
        $backup = Scalr_Db_Backup::init()->loadById($backupId);

        $this->user->getPermissions()->validate($backup);

        $data = array(
            'backup_id' => $backup->id,
            'farm_id'	=> $backup->farmId,
            'type'		=> ROLE_BEHAVIORS::GetName($backup->service) ? ROLE_BEHAVIORS::GetName($backup->service) : 'unknown',
            'date'		=> Scalr_Util_DateTime::convertTz($backup->dtCreated),
            'size'		=> $backup->size ? round($backup->size / 1024 / 1024, 2) : 0,
            'provider'	=> $backup->provider,
            'cloud_location' => $backup->cloudLocation,
            'farmName'	=> DBFarm::LoadByIDOnlyName($backup->farmId)
        );
        $downloadParts = $backup->getParts();

        foreach ($downloadParts as $part) {
            $part['size'] = $part['size'] ? round($part['size']/1024/1024, 2) : '';
            if ($part['size'] == 0)
                $part['size'] = 0.01;

            if ($data['provider'] == 's3')
                $part['link'] = $this->getS3SignedUrl($part['path']);
            else if ($data['provider'] == 'cf')
                $part['link'] = $this->getCfSignedUrl($part['path'], $data['cloud_location']);
            else
                continue;

            $part['path'] = pathinfo($part['path']);
            $links[$part['number']] = $part;
        }
        $data['links'] = $links;
        return $data;
    }

    public function xRemoveBackupAction()
    {
        $backup = Scalr_Db_Backup::init()->loadById($this->getParam('backupId'));
        $this->user->getPermissions()->validate($backup);

        $backup->delete();
        $this->response->success('Backup successfully queued for removal.');
    }

    private function getS3SignedUrl($path)
    {
         $bucket = substr($path, 0, strpos($path, '/'));
         $resource = substr($path, strpos($path, '/') + 1, strlen($path));
         $expires = time() + 3600;

         $AWSAccessKey = $this->getEnvironment()->getPlatformConfigValue(Modules_Platforms_Ec2::ACCESS_KEY);
         $AWSSecretKey = $this->getEnvironment()->getPlatformConfigValue(Modules_Platforms_Ec2::SECRET_KEY);

         $stringToSign = "GET\n\n\n{$expires}\n/" . str_replace(".s3.amazonaws.com", "", $bucket) . "/{$resource}";
         $signature = urlencode(
                         base64_encode(
                            hash_hmac( "sha1", utf8_encode( $stringToSign ), $AWSSecretKey, TRUE )
                        )
                     );

         $authenticationParams = "AWSAccessKeyId={$AWSAccessKey}&Expires={$expires}&Signature={$signature}";

         return $link = "http://{$bucket}.s3.amazonaws.com/{$resource}?{$authenticationParams}";
    }

    private function getCfSignedUrl($path, $location)
    {
        $expires = time() + 3600;

        $user = $this->getEnvironment()->getPlatformConfigValue(Modules_Platforms_Rackspace::USERNAME, true, $location);
        $key = $this->getEnvironment()->getPlatformConfigValue(Modules_Platforms_Rackspace::API_KEY, true, $location);

        $cs = Scalr_Service_Cloud_Rackspace::newRackspaceCS($user, $key, $location);
        $auth = $cs->authToReturn();

        $stringToSign = "GET\n\n\n{$expires}\n/{$path}";
        $signature = urlencode(
                        base64_encode(
                            hash_hmac("sha1", utf8_encode( $stringToSign ), $key, true)
                        )
                    );

        $authenticationParams = "temp_url_sig={$signature}&temp_url_expires={$expires}";

        $link = "{$auth['X-Cdn-Management-Url']}/{$path}?{$authenticationParams}";
        return $link;
    }
}