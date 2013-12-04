<?php

use Scalr\Service\Aws\S3\DataType\BucketData;
use Scalr\Service\Aws\CloudFront\DataType\DistributionData;
use Scalr\Service\Aws\CloudFront\DataType\DistributionConfigData;
use Scalr\Service\Aws\CloudFront\DataType\DistributionConfigOriginData;
use Scalr\Service\Aws\CloudFront\DataType\DistributionConfigAliasList;
use Scalr\Service\Aws\CloudFront\DataType\DistributionConfigOriginList;
use Scalr\Service\Aws\CloudFront\DataType\CacheBehaviorData;
use Scalr\Service\Aws\CloudFront\DataType\DistributionS3OriginConfigData;

class Scalr_UI_Controller_Tools_Aws_S3 extends Scalr_UI_Controller
{
    public function hasAccess()
    {
        $enabledPlatforms = $this->getEnvironment()->getEnabledPlatforms();
        if (!in_array(SERVER_PLATFORMS::EC2, $enabledPlatforms))
            throw new Exception("You need to enable EC2 platform for current environment");

        return true;
    }

    public function defaultAction()
    {
        $this->manageBucketsAction();
    }

    public function manageBucketsAction()
    {
        $this->response->page('ui/tools/aws/s3/buckets.js', array(
            'locations'	=> self::loadController('Platforms')->getCloudLocations(SERVER_PLATFORMS::EC2, false)
        ));
    }

    public function xListBucketsAction()
    {
        $aws = $this->environment->aws;
        $distributions = array();
        //Retrieves the list of all distributions
        $distList = $aws->cloudFront->distribution->describe();
        /* @var $dist DistributionData */
        foreach ($distList as $dist) {
            /* @var $org DistributionConfigOriginData */
            foreach ($dist->distributionConfig->origins as $org) {
                $distributions[preg_replace('#\.s3\.amazonaws\.com$#', '', $org->domainName)] = $dist;
            }
            unset($dist);
        }

        // Get list of all user buckets
        $buckets = array();
        /* @var $bucket BucketData */
        foreach ($aws->s3->bucket->getList() as $bucket) {
            $bucketName = $bucket->bucketName;
            if (empty($distributions[$bucketName])) {
                $info = array(
                    "name" => $bucketName
                );
            } else {
                $dist = $distributions[$bucketName];
                $info = array(
                    "name"    => $bucketName,
                    "cfid"    => $dist->distributionId,
                    "cfurl"   => $dist->domainName,
                    "cname"   => $dist->distributionConfig->aliases->get(0)->cname,
                    "status"  => $dist->status,
                    "enabled" => $dist->distributionConfig->enabled ? 'true' : 'false'
                );
            }

            $c = explode("-", $info['name']);
            if ($c[0] == 'farm') {
                $hash = $c[1];

                $farm = $this->db->GetRow("SELECT id, name FROM farms WHERE hash=? AND env_id = ?", array($hash, $this->environment->id));
                if ($farm) {
                    $info['farmId'] = $farm['id'];
                    $info['farmName'] = $farm['name'];
                }
            }

            $buckets[] = $info;
        }

        $response = $this->buildResponseFromData($buckets, array('name', 'farmName'));
        $this->response->data($response);
    }

    public function xCreateBucketAction ()
    {
        $aws = $this->environment->aws;
        $aws->s3->bucket->create($this->getParam('bucketName'), $this->getParam('location'));
        $this->response->success('Bucket successfully created');
    }

    public function xDeleteBucketAction ()
    {
        $this->request->defineParams(array(
            'buckets' => array('type' => 'json')
        ));

        $aws = $this->environment->aws;
        foreach ($this->getParam('buckets') as $bucketName)
            $aws->s3->bucket->delete($bucketName);

        $this->response->success('Bucket(s) successfully deleted');
    }

    public function manageDistributionAction ()
    {
        $this->response->page('ui/tools/aws/s3/distribution.js');
    }

    public function xCreateDistributionAction ()
    {
        $aws = $this->environment->aws;
        $distributionConfig = new DistributionConfigData();
        if ($this->getParam('localDomain') && $this->getParam('zone')) {
            $distributionConfig->aliases->append(array(
                'cname' => $this->getParam('localDomain') . '.' . $this->getParam('zone'),
            ));
        } else if ($this->getParam('remoteDomain')) {
            $distributionConfig->aliases->append(array(
                'cname' => $this->getParam('remoteDomain'),
            ));
        }
        $distributionConfig->comment = $this->getParam('comment');
        $distributionConfig->enabled = true;
        $origin = new DistributionConfigOriginData('MyOrigin', $this->getParam('bucketName') . ".s3.amazonaws.com");
        $origin->setS3OriginConfig(new DistributionS3OriginConfigData());
        $distributionConfig->origins->append($origin);
        $distributionConfig->priceClass = DistributionConfigData::PRICE_CLASS_ALL;
        $distributionConfig->setDefaultCacheBehavior(
            new CacheBehaviorData($origin->originId, CacheBehaviorData::VIEWER_PROTOCOL_POLICY_ALLOW_ALL, 3600)
        );

        $result = $aws->cloudFront->distribution->create($distributionConfig);

        $this->db->Execute("INSERT INTO distributions SET
                cfid	= ?,
                cfurl	= ?,
                cname	= ?,
                zone	= ?,
                bucket	= ?,
                clientid= ?
            ",
                array(
                    $result->distributionId,
                    $result->domainName,
                    $this->getParam('localDomain') ? $this->getParam('localDomain') : $result->distributionConfig->aliases[0]->cname,
                    $this->getParam('zone')? $this->getParam('zone') : $result->distributionConfig->aliases[0]->cname,
                    $this->getParam('bucketName'),
                    $this->user->getAccountId()
                )
            );

//         $zoneinfo = $this->db->GetRow("SELECT * FROM dns_zones WHERE zone_name=? AND client_id=?",
//             array(
//             $this->getParam('zone')? $this->getParam('zone') : $distributionConfig->CNAME,
//             $this->user->getAccountId()
//         ));
//         if ($zoneinfo)
//         {
//             $this->db->Execute("INSERT INTO dns_zone_records SET
//                 zone_id	= ?,
//                 type	= ?,
//                 ttl		= ?,
//                 name	= ?,
//                 value	= ?,
//                 issystem= ?
//             ", array($zoneinfo['id'], 'CNAME', 14400, $distributionConfig->CNAME, $result['DomainName'], 0));
//         }

        $this->response->success("Distribution successfully created");
    }

    public function xUpdateDistributionAction ()
    {
        $aws = $this->environment->aws;

        $dist = $aws->cloudFront->distribution->fetch($this->getParam('id'));
        $dist->distributionConfig->enabled = ($this->getParam('enabled') == 'true');
        $dist->setConfig($dist->distributionConfig, $dist->getETag());

        $this->response->success("Distribution successfully updated");
    }

    public function xDeleteDistributionAction ()
    {
        $aws = $this->environment->aws;

        $dist = $aws->cloudFront->distribution->fetch($this->getParam('id'));
        $result = $dist->delete();

        $info = $this->db->GetRow("SELECT * FROM distributions WHERE cfid=?", array($this->getParam('id')));

        if ($info) {
            $this->db->Execute("DELETE FROM distributions WHERE cfid=?", array($this->getParam('id')));

            // Remove CNAME from DNS zone
//             $zoneinfo = $this->db->GetRow("SELECT * FROM dns_zones WHERE zone_name=? AND client_id=?",
//                 array($info['zone'], $this->user->getAccountId())
//             );

//             if ($zoneinfo) {
//                 $this->db->Execute("DELETE FROM dns_zone_records WHERE
//                     zone_id	= ? AND
//                     type	= ? AND
//                     name	= ? AND
//                     value	= ?
//                 ", array($zoneinfo['id'], 'CNAME', $this->getParam('cname'), $this->getParam('cfurl')));
//             }
        }

        $this->response->success("Distribution successfully removed");
    }

    public function xListZonesAction()
    {
        $zones = $this->db->GetAll("SELECT zone_name FROM dns_zones WHERE status!=? AND env_id=?",
            array(DNS_ZONE_STATUS::PENDING_DELETE,
            $this->getEnvironmentId())
        );
        $this->response->data(array('data'=>$zones));
    }
}
