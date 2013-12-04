<?php

use Scalr\Service\Aws\Client\ClientException;

define("NO_TEMPLATES", 1);

require_once __DIR__ . '/../src/prepend.inc.php';

set_time_limit(0);

$ScalrUpdate = new Update20130401();
$ScalrUpdate->Run();

class Update20130401
{
    public function Run()
    {
        global $db;

        $time = microtime(true);

        $rs = $db->Execute("
            SELECT DISTINCT c1.`env_id`
            FROM `client_environment_properties` c1
            JOIN `client_environment_properties` c2 ON c2.env_id = c1.env_id
            WHERE c1.`name` = 'cloudyn.enabled' AND c1.`value` = '1'
            AND c2.`name` = 'ec2.is_enabled' AND c2.`value` = '1'
        ");
        foreach ($rs as $row) {
            $env = Scalr_Environment::init();
            $env->loadById($row['env_id']);
            $awsUsername = sprintf('scalr-cloudyn-%s-%s', $env->id, SCALR_ID);
            $policyName = sprintf('cloudynpolicy-%s', $env->id);
            try {
                $policy = $env->aws->iam->user->getUserPolicy($awsUsername, $policyName);
                $aPolicy = json_decode($policy, true);
                $bUpdated = false;
                if (!empty($aPolicy['Statement'])) {
                    foreach ($aPolicy['Statement'] as $k => $v) {
                        if (!isset($v['Effect']) || $v['Effect'] != 'Allow') continue;
                        if (!empty($v['Action']) && is_array($v['Action'])) {
                            $ptr =& $aPolicy['Statement'][$k]['Action'];
                            if (!in_array("rds:List*", $ptr)) {
                                $ptr[] = "rds:List*";
                                $bUpdated = true;
                            }
                            if (!in_array("s3:GetBucketTagging", $ptr)) {
                                $ptr[] = "s3:GetBucketTagging";
                                $bUpdated = true;
                            }
                            unset($ptr);
                            if ($bUpdated) {
                                $env->aws->iam->user->putUserPolicy($awsUsername, $policyName, json_encode($aPolicy));
                                break;
                            }
                        }
                    }
                }
            } catch (ClientException $e) {
                echo $e->getMessage() . "\n";
            }
            unset($env);
        }


        print "Done.\n";

        $t = round(microtime(true) - $time, 2);

        printf("Upgrade process took %0.2f seconds\n\n\n", $t);
    }

    public function migrate()
    {
    }
}