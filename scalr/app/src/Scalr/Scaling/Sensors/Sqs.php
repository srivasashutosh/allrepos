<?php

class Scalr_Scaling_Sensors_Sqs extends Scalr_Scaling_Sensor
{

    const SETTING_QUEUE_NAME = 'queue_name';

    public function __construct()
    {

    }

    public function getValue(DBFarmRole $dbFarmRole, Scalr_Scaling_FarmRoleMetric $farmRoleMetric)
    {
        $sqs = $dbFarmRole->GetFarmObject()->GetEnvironmentObject()->aws($dbFarmRole)->sqs;
        try {
            $sqs->enableEntityManager();
            $queue = $sqs->queue->getAttributes($farmRoleMetric->getSetting(self::SETTING_QUEUE_NAME));
            $retval = $queue->approximateNumberOfMessages;
        } catch (Exception $e) {
            throw new Exception(sprintf("SQSScalingSensor failed during SQS request: %s", $e->getMessage()));
        }
        return array($retval);
    }
}