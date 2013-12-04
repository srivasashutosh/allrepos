<?php

use Scalr\Service\Aws\CloudWatch\DataType\DimensionFilterData;
use Scalr\Service\Aws\CloudWatch\DataType\DimensionData;
use Scalr\Service\Aws\CloudWatch\DataType\DatapointData;
use Scalr\Service\Aws\CloudWatch\DataType\MetricData;

class Scalr_UI_Controller_Tools_Aws_Ec2_Cloudwatch extends Scalr_UI_Controller
{
    public function xEnableAction() {
        $dbServer = DBServer::LoadByID($this->getParam('serverId'));
        $this->user->getPermissions()->validate($dbServer);

        $aws = $this->getEnvironment()->aws($dbServer->GetCloudLocation());
        $aws->ec2->instance->monitor($dbServer->GetProperty(EC2_SERVER_PROPERTIES::INSTANCE_ID));

        $this->response->success("Cloudwatch monitoring successfully enabled for the instance");
    }

    public function xDisableAction() {
        $dbServer = DBServer::LoadByID($this->getParam('serverId'));
        $this->user->getPermissions()->validate($dbServer);

        $aws = $this->getEnvironment()->aws($dbServer->GetCloudLocation());
        $aws->ec2->instance->unmonitor($dbServer->GetProperty(EC2_SERVER_PROPERTIES::INSTANCE_ID));

        $this->response->success("Cloudwatch monitoring successfully enabled for the instance");
    }

    public function viewAction()
    {
        $cloudWatch = $this->getEnvironment()->aws($this->getParam('region'))->cloudWatch;
        $namespace = $this->getParam('namespace') ? $this->getParam('namespace') : "AWS/EC2";
        $metricList = $cloudWatch->metric->list(
            new DimensionFilterData($this->getParam('object'), $this->getParam('objectId')),
            $namespace
        );
        $aMetric = array();
        $statistics = array('Average');
        /* @var $metric MetricData */
        foreach ($metricList as $metric) {
            $datapointList = $cloudWatch->metric->getStatistics(
                $metric->metricName,
                new \DateTime('-3600 second', new DateTimeZone('UTC')),
                new \DateTime(null, new DateTimeZone('UTC')),
                $statistics,
                $namespace,
                300,
                null,
                new DimensionData($this->getParam('object'), $this->getParam('objectId'))
            );

            $dps = array();
            $unit = null;
            //Ensures backward compability for the result array
            /* @var $datapoint DatapointData */
            foreach ($datapointList as $datapoint) {
                $unit = $datapoint->unit;
                foreach ($statistics as $s) {
                    $lcs = lcfirst($s);
                    if ($datapoint->unit == 'Bytes' || $datapoint->unit == 'Bytes/Second') {
                        $dps[$datapoint->timestamp->getTimestamp()][$s] = round($datapoint->{$lcs} / 1024, 2);
                    } else {
                        $dps[$datapoint->timestamp->getTimestamp()][$s] = $datapoint->{$lcs};
                    }
                }
            }

            $maxAverage = null;
            /* @var $datapoint DatapointData */
            foreach ($dps as $value) {
                if ($unit == "Bytes" || $unit == "Bytes/Second") {
                    if ($maxAverage === null || $maxAverage < $value['Average']) {
                        $maxAverage = $value['Average'];
                    }
                }
            }
            if ($maxAverage !== null) {
                if ($maxAverage >= 1024 && $maxAverage <= 1048576) {
                    $unit = "M" . $unit;
                } else if ($maxAverage > 1048576 && $maxAverage <= 1048576 * 1024) {
                    $unit = "G" . $unit;
                } else if ($maxAverage < 1024) {
                    $unit = "K" . $unit;
                }
            }
            $aMetric[] = array(
                'name' => $metric->metricName,
                'unit' => $unit
            );
        }

        $this->response->page('ui/tools/aws/ec2/cloudwatch/view.js', array('metric' => $aMetric), array('extjs-4.1/ext-chart.js'));
    }

    public function xGetMetricAction()
    {
        $cloudWatch = $this->getEnvironment()->aws($this->getParam('region'))->cloudWatch;

        $statistics = array($this->getParam('type'));
        $datapointList = $cloudWatch->metric->getStatistics(
            $this->getParam('metricName'),
            new \DateTime($this->getParam('startTime')),
            new \DateTime($this->getParam('endTime')),
            $statistics,
            $this->getParam('namespace'),
            $this->getParam('period'),
            null,
            new DimensionData($this->getParam('dValue'), $this->getParam('dType'))
        );

        $dps = array();
        $unit = null;
        //Ensures backward compability for the result array
        /* @var $datapoint DatapointData */
        foreach ($datapointList as $datapoint) {
            $unit = $datapoint->unit;
            foreach ($statistics as $s) {
                $lcs = lcfirst($s);
                if ($datapoint->unit == 'Bytes' || $datapoint->unit == 'Bytes/Second') {
                    $dps[$datapoint->timestamp->getTimestamp()][$s] = round($datapoint->{$lcs} / 1024, 2);
                } else {
                    $dps[$datapoint->timestamp->getTimestamp()][$s] = $datapoint->{$lcs};
                }
            }
        }

        $store = array();
        ksort($dps);
        foreach ($dps as $time => $val) {
            if ($this->getParam('Unit') == "MBytes" || $this->getParam('Unit') == "MBytes/Second") {
                $store[] = array(
                    'time'  => date($this->getParam('dateFormat'), $time),
                    'value' => (float) round($val[$this->getParam('type')] / 1024, 2)
                );
            } else if ($this->getParam('Unit') == "GBytes" || $this->getParam('Unit') == "GBytes/Second") {
                $store[] = array(
                    'time'  => date($this->getParam('dateFormat'), $time),
                    'value' => (float) round($val[$this->getParam('type')] / 1024 / 1024, 2)
                );
            } else {
                $store[] = array(
                    'time'  => date($this->getParam('dateFormat'), $time),
                    'value' => (float) round($val[$this->getParam('type')], 2)
                );
            }
        }

        $this->response->data(array('data' => $store));
    }
}
