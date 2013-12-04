<?php
class Scalr_UI_Controller_Dashboard_Widget_Status extends Scalr_UI_Controller_Dashboard_Widget
{
    public function getDefinition()
    {
        return array(
            'type' => 'nonlocal'
        );
    }

    public function getContent($params = array())
    {
        $awsCachePath = CACHEPATH."/aws.status.cxml";
        $data = array();
        $neededLocations = array();
        $services = array('Amazon Elastic Compute Cloud', 'Amazon Relational Database Service', 'Amazon Simple Storage Service');
        $compliance = array (
            'us-east-1' => array(
                'name' => 'NA_block',
                'filter' => array(
                    'N. Virginia',
                    'US Standard'
                )
            ),
            'us-west-1' => array(
                'name' => 'NA_block',
                'filter' => 'N. California'
            ),
            'us-west-2' => array(
                'name' => 'NA_block',
                'filter' => 'Oregon'
            ),
            'sa-east-1' => array(
                'name' => 'SA_block',
                'filter' => ''
            ),
            'eu-west-1' => array(
                'name' => 'EU_block',
                'filter' => ''
            ),
            'ap-southeast-1' => array(
                'name' => 'AP_block',
                'filter' => 'Singapore'
            ),
            'ap-southeast-2' => array(
                'name' => 'AP_block',
                'filter' => 'Sydney'
            ),
            'ap-northeast-1' => array(
                'name' => 'AP_block',
                'filter' => 'Tokyo'
            )
        );
        if (empty($params['locations'])) {
            $neededLocations = $this->getUsedLocations();
            $params['locations'] = $neededLocations;
        } else
            $neededLocations = $params['locations'];

        if (file_exists($awsCachePath) && (time() - filemtime($awsCachePath) < 3600)) {
            clearstatcache();
            $time = filemtime($awsCachePath);
            $data = (array)json_decode(file_get_contents($awsCachePath));
        } else {
            $html = @file_get_contents('http://status.aws.amazon.com');
            if ($html) {
                $dom = new domDocument;
                $dom->loadHTML($html);
                $dom->preserveWhiteSpace = false;

                foreach ($compliance as $compKey=>$compValue) {
                    $div = $dom->getElementById($compValue['name']);
                    $tables = $div->getElementsByTagName('table');
                    $rows = $tables->item(0)->getElementsByTagName('tr');

                    foreach ($rows as $row)
                    {
                        $cols = $row->getElementsByTagName('td');
                        if(preg_match('/(.*)('.implode('|',$services).')(.*)/', $cols->item(1)->nodeValue)) {
                            $regionFilter = $compValue['filter'];
                            if(is_array($compValue['filter']))
                                $regionFilter = implode('|', $compValue['filter']);
                            if(preg_match('/(.*)('.$regionFilter.')(.*)/', $cols->item(1)->nodeValue)) {
                                $img = '';
                                $message = '';
                                if($cols->item(0)->getElementsByTagName('img')->item(0)->getAttribute('src') == 'images/status0.gif') {
                                    $img = 'normal.png';
                                } else {
                                    $img = 'disruption.png';
                                    $message = $cols->item(2)->nodeValue;
                                }
                                $data[$compKey][substr(str_replace( $services, array('EC2', 'RDS', 'S3'), $cols->item(1)->nodeValue), 0, strpos(str_replace( $services, array('EC2', 'RDS', 'S3'), $cols->item(1)->nodeValue), ' ('))] = array(
                                    'img' => $img,
                                    'status' => $cols->item(2)->nodeValue,
                                    'message'=> $message
                                );
                                $data[$compKey]['locations'] = $compKey;
                            }
                        }
                    }

                }

                file_put_contents($awsCachePath, json_encode($data));
            }
        }
        $retval = array('locations' => json_encode($neededLocations));
        foreach ($neededLocations as $value) {
            $retval['data'][] = $data[$value];
        }
        return $retval;
    }

    public function xGetContentAction ()
    {
        $this->request->defineParams(array(
            'locations' => array('type' => 'json')
        ));

        $this->response->data(
            $this->getContent(array(
                    'locations' => $this->request->getParam('locations')
                )
            )
        );
    }

    public function xGetLocationsAction ()
    {
        $this->response->data(array(
            'locations' => self::loadController('Platforms')->getCloudLocations(SERVER_PLATFORMS::EC2, false)
        ));
    }

    public function getUsedLocations() {
        $locationResults = $this->db->Execute('SELECT DISTINCT(value) FROM server_properties WHERE server_id IN (SELECT server_id FROM servers WHERE env_id=?) AND `name`= ?', array($this->getEnvironmentId(), EC2_SERVER_PROPERTIES::REGION));
        $neededLocations = array();
        while ($location = $locationResults->fetchRow()) {
            $neededLocations[] = $location['value'];
        }
        return $neededLocations;
    }
}