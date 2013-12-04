<?php
class Scalr_UI_Controller_Statistics extends Scalr_UI_Controller
{
    public function defaultAction()
    {
        $this->serversUsageAction();
    }

    public function serversUsageAction()
    {
        $years = array();
        $results = $this->db->GetAll('SELECT `year` FROM servers_stats GROUP BY `year`');
        foreach ($results as $key => $value)
            $years[] = $value['year'];

        $envs = array();
        $envs[0] = 'All environments';
        foreach($this->user->getEnvironments() as $key => $value)
            $envs[$value['id']] = $value['name'];

        $this->response->page('ui/statistics/serversusage.js', array('years'=> $years, 'env'=> $envs, 'price'=>$this->getInstancePrice()));
    }

    public function getInstancePrice()
    {
        $priceList = file_get_contents('http://aws.amazon.com/ec2/pricing/pricing-on-demand-instances.json');
        $priceList = (array)json_decode($priceList);
        $priceList = $priceList['config']->regions;
        $price = array();
        $compliance = array(
            'u' => 'micro',
            'sm' => 'small',
            'med' => 'medium',
            'lg' => 'large',
            'xl' => 'xlarge',
            'xxl' => '2xlarge',
            'xxxxl' => '4xlarge',
            'xxxxxxxxl' => '8xlarge',
            'stdODI' => 'm1',
            'uODI' => 't1',
            'hiMemODI' => 'm2',
            'hiCPUODI' => 'c1',
            'clusterComputeI' => 'cc1',
            'clusterGPUI' => 'cg1',
            'hiIoODI' => 'hi1'
        );
        foreach ((array) $priceList as $row) {
            foreach ($row->instanceTypes as $type) {
                $type = (array) $type;
                foreach ($type['sizes'] as $size) {
                    $size = (array)$size;
                    $region = str_replace(
                        array(
                            'us-east', 'apac-sin', 'apac-syd', 'apac-tokyo', 'eu-ireland'
                        ), array(
                            'us-east-1', 'ap-southeast-1', 'ap-southeast-2', 'ap-northeast-1', 'eu-west-1'
                        ),
                        $row->region
                    );

                    if ($region == "us-west")
                        $region = 'us-west-1';

                    $iType = isset($compliance[$type['type']]) ? $compliance[$type['type']] : '';

                    if (isset($compliance[$size['size']]) && $compliance[$size['size']] == '8xlarge')
                        $iType = 'cc2';

                    $price[$region][$iType . '.' . (isset($compliance[$size['size']]) ? $compliance[$size['size']] : '')] = $size['valueColumns'][0]->prices->USD;
                }

            }

        }
        return $price;
    }

    public function xListFarmsAction()
    {
        // Check permissions
        if ($this->getParam('envId') == '0' && $this->user->getType() == Scalr_Account_User::TYPE_ACCOUNT_OWNER) {
            $data = $this->db->GetAll("SELECT id, name FROM farms WHERE clientid = ?", $this->user->getAccountId());
        } else {
            $this->user->getPermissions()->validate(Scalr_Environment::init()->loadById($this->getParam('envId')));
            $data = $this->db->GetAll("SELECT id, name FROM farms WHERE env_id = ?", $this->getParam('envId'));
        }

        array_unshift($data, array(
            'id' => '0',
            'name' => 'All farms'
        ));

        $this->response->data(array('data'=> $data));
    }

    public function xListServersUsageAction()
    {
        foreach($this->user->getEnvironments() as $key => $value)
            $env[] = $value['id'];
        $env = implode(',',$env);
        $params = array($this->getParam('year'));

        $sql = 'SELECT SUM(`usage`) as `usage`, `month`, `instance_type` as `instanceType`, `cloud_location` as `cloudLocation` FROM `servers_stats` WHERE `year` = ?';
        if($this->getParam('envId') != 0) {
            $sql.= " AND `env_id` = ?";

            $this->user->getPermissions()->validate(Scalr_Environment::init()->loadById($this->getParam('envId')));

            $params[] = $this->getParam('envId');
        }else
            $sql.= " AND `env_id` IN (".$env.")";
        if($this->getParam('farmId') != 0) {
            $sql.= " AND `farm_id` = ?";

            $this->user->getPermissions()->validate(DBFarm::LoadByID($this->getParam('farmId')));

            $params[] = $this->getParam('farmId');
        }
        $sql.= 'GROUP BY `month`, `instance_type`, `cloud_location`';

        $usages = $this->db->GetAll($sql, $params);
        $result = array();

        foreach ($usages as $value) {
            $key = "{$value['cloudLocation']}-{$value['instanceType']}";
            if (! isset($result[$key])) {
                $result[$key] = array(
                    'cloudLocation' => $value['cloudLocation'],
                    'instanceType' => $value['instanceType'],
                    'usage' => array()
                );
            }

            $result[$key]['usage'][date( 'F', mktime(0, 0, 0, $value['month']))] = round(($value['usage'] / 60), 2);
        }

        $response = $this->buildResponseFromData($result);
        if ($this->getParam('action') == "download") {
            $fileContent = array();
            $fileContent[] = "cloudLocation;instanceType;Jan;Feb;Mar;Apr;May;Jun;Jul;Aug;Sep;Oct;Nov;Dec\r\n";

            foreach($response["data"] as $data) {
                $fileContent[] = "{$data['cloudLocation']};{$data['instanceType']};{$data['usage']['Jan']};{$data['usage']['Feb']};{$data['usage']['Mar']};{$data['usage']['Apr']};{$data['usage']['May']};{$data['usage']['Jun']};{$data['usage']['Jul']};{$data['usage']['Aug']};{$data['usage']['Sep']};{$data['usage']['Oct']};{$data['usage']['Nov']};{$data['usage']['Dec']}";
            }

            $this->response->setHeader('Content-Encoding', 'utf-8');
            $this->response->setHeader('Content-Type', 'text/csv', true);
            $this->response->setHeader('Expires', 'Mon, 10 Jan 1997 08:00:00 GMT');
            $this->response->setHeader('Pragma', 'no-cache');
            $this->response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate');
            $this->response->setHeader('Cache-Control', 'post-check=0, pre-check=0');
            $this->response->setHeader('Content-Disposition', 'attachment; filename=' . "usageStatistic_" . Scalr_Util_DateTime::convertTz(time(), 'M-j-Y') . ".csv");
            $this->response->setResponse(implode("\n", $fileContent));
        } else
            $this->response->data($response);
    }
}