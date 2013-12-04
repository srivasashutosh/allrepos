<?php
class Scalr_UI_Controller_Dashboard_Widget_Monitoring extends Scalr_UI_Controller_Dashboard_Widget
{
    public function getDefinition()
    {
        return array(
            'type' => 'local'
        );
    }

    public function getContent($params = array())
    {
        $STATS_URL = 'http://monitoring.scalr.net';

        $content = @file_get_contents("{$STATS_URL}/server/statistics.php?".http_build_query(array(
            'version'=> 2,
            'task'=>'get_stats_image_url',
            'farmid'=> $params['farmid'],
            'watchername'=> $params['watchername'],
            'graph_type'=> $params['graph_type'],
            'role' => $params['role']
        )));

        return json_decode($content);
    }
}