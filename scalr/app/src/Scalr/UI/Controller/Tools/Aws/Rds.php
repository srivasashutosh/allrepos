<?php

class Scalr_UI_Controller_Tools_Aws_Rds extends Scalr_UI_Controller
{
    public function hasAccess()
    {
        $enabledPlatforms = $this->getEnvironment()->getEnabledPlatforms();
        if (!in_array(SERVER_PLATFORMS::EC2, $enabledPlatforms))
            throw new Exception("You need to enable RDS platform for current environment");

        return true;
    }

    public function logsAction()
    {
        $this->response->page('ui/tools/aws/rds/logs.js');
    }

    public function xListLogsAction()
    {
        $aws = $this->getEnvironment()->aws($this->getParam('cloudLocation'));

        $request = new \Scalr\Service\Aws\Rds\DataType\DescribeEventRequestData();
        $request->sourceIdentifier = $this->getParam('name') ?: null;
        $request->sourceType = $this->getParam('type') ?: null;
        $events = $aws->rds->event->describe($request);
        $logs = array();
        /* @var $event \Scalr\Service\Aws\Rds\DataType\EventData */
        foreach ($events as $event) {
            if ($event->message) {
                $logs[] = array(
                    'Message' => $event->message,
                    'Date' => $event->date,
                    'SourceIdentifier' => $event->sourceIdentifier,
                    'SourceType' => $event->sourceType,
                );
            }
        }
        $response = $this->buildResponseFromData($logs, array('Date', 'Message'));
        foreach ($response['data'] as &$row) {
            $row['Date'] = Scalr_Util_DateTime::convertTz($row['Date']);
        }

        $this->response->data($response);
    }
}
