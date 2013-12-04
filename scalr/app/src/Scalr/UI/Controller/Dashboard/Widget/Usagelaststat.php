<?php
class Scalr_UI_Controller_Dashboard_Widget_Usagelaststat extends Scalr_UI_Controller_Dashboard_Widget
{
    public function getDefinition()
    {
        return array(
            'type' => 'local'
        );
    }

    public function getContent($params = array())
    {
        if (!$params['farmCount'])
            $params['farmCount'] = 10;

        $currentTime = getdate();
        $years = array($currentTime['year']);
        $months = array();
        $months['current'] = $currentTime['mon'];
        if($months['current'] == 1)
            $years[1] = $currentTime['year']-1;
        $months['recent'] = $months['current'] == 1 ? $months['recent'] = 12 : $months['current'] - 1;
        $implodedMonths = implode(', ', $months);
        $implodedYears = implode(', ', $years);

        $sql = "SELECT `usage`, `month`, `farms`.`name` AS farm, `farm_id`, `instance_type`, `cloud_location`
                FROM `servers_stats`
                LEFT JOIN `farms` ON `servers_stats`.`farm_id` = `farms`.`id`
                WHERE `servers_stats`.`env_id` = ?
                AND `year` IN ({$implodedYears})
                AND `month` IN ({$implodedMonths})";

        if ($params['farmCount'] != 'all') {
            $farmResult = $this->db->GetAll("
                SELECT DISTINCT(`farm_id`), `farms`.`name`
                FROM `servers_stats`
                LEFT JOIN `farms` ON `servers_stats`.`farm_id` = `farms`.`id`
                WHERE `servers_stats`.`env_id` = ?
                AND year IN ({$implodedYears})
                AND month IN ({$implodedMonths})",
                    array( $this->getEnvironmentId()
            ));
            $farms = array();
            foreach ($farmResult as $val) {
                if($val['name'] && count($farms) < $params['farmCount'])
                    $farms[$val['farm_id']] = $val['farm_id'];
            }
            if (count($farms) < $params['farmCount']) {
                foreach ($farmResult as $val) {
                    if(!$farms[$val['farm_id']] && count($farms) < $params['farmCount'])
                        $farms[$val['farm_id']] = $val['farm_id'];
                }
            }

            if (count($farms) > 0)
                $sql.=	" AND `farm_id` IN (".implode(', ', $farms).")";
            else
                $sql.= " AND 1 = 1";
        }

        $usages = $this->db->Execute($sql, array($this->getEnvironmentId()));
        $price = self::loadController('Statistics')->getInstancePrice();
        $stat = array();

        while ($value = $usages->FetchRow()) {
            $month = 'current';
            if ($value['month'] == $months['recent'])
                $month = 'recent';

            $farmName = $value['farm'] ? $value['farm']: '*removed farm*';
            if (!$stat['farms'][$farmName.$value['farm_id']]) {
                $stat['farms'][$farmName.$value['farm_id']]['farm'] = $farmName;
                $stat['farms'][$farmName.$value['farm_id']]['farm_id'] = $value['farm_id'];
                $stat['farms'][$farmName.$value['farm_id']]['current'] = 0;
                $stat['farms'][$farmName.$value['farm_id']]['recent'] = 0;
            }
            $stat['farms'][$farmName.$value['farm_id']][$month] += round($price[$value['cloud_location']][$value['instance_type']] * round(($value['usage'] / 60), 2), 2);
        }

        if (isset($stat['farms']) && is_array($stat['farms']))
            arsort($stat['farms']);

        return $stat;
    }
}