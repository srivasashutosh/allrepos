<?php
namespace Scalr\Tests\Service\Aws;

use Scalr\Service\Aws\Rds\DataType\DescribeEventRequestData;
use Scalr\Service\Aws\Rds\DataType\DBSnapshotData;
use Scalr\Service\Aws\Rds\DataType\ParameterData;
use Scalr\Service\Aws\Rds\DataType\DBParameterGroupData;
use Scalr\Service\Aws\Rds\DataType\DBSecurityGroupIngressRequestData;
use Scalr\Service\Aws\Rds\DataType\EC2SecurityGroupData;
use Scalr\Service\Aws\Rds\DataType\IPRangeData;
use Scalr\Service\Aws\Rds\DataType\DBSecurityGroupData;
use Scalr\Service\Aws\Rds\DataType\VpcSecurityGroupMembershipData;
use Scalr\Service\Aws\Rds\DataType\DBParameterGroupStatusData;
use Scalr\Service\Aws\Rds\DataType\DBSecurityGroupMembershipData;
use Scalr\Service\Aws\Rds\DataType\ModifyDBInstanceRequestData;
use Scalr\Service\Aws\DataType\ErrorData;
use Scalr\Service\Aws\Rds\DataType\DBInstanceData;
use Scalr\Service\Aws\Rds\DataType\CreateDBInstanceRequestData;
use Scalr\Service\Aws\Rds\DataType as RdsDataType;
use Scalr\Service\Aws\Client\ClientException;
use Scalr\Service\Aws\Client\ClientResponseInterface;
use Scalr\Service\Aws;
use Scalr\Service\Aws\Rds;
use Scalr\Tests\Service\AwsTestCase;
use \SplFileInfo;

/**
 * Amazon Rds Test
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     07.03.2013
 */
class RdsTest extends AwsTestCase
{

    const NAME_SG = 'dbsg';

    const NAME_INSTANCE = 'dbi';

    const NAME_DB_PARAMETER_GROUP = 'dbpg';

    const NAME_DB_SNAPSHOT = 'snshot';

    /**
     * {@inheritdoc}
     * @see Scalr\Tests\Service.AwsTestCase::getFixturesDirectory()
     */
    public function getFixturesDirectory()
    {
        return parent::getFixturesDirectory() . '/Rds';
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Tests\Service.AwsTestCase::getFixtureFilePath()
     */
    public function getFixtureFilePath($filename)
    {
        return $this->getFixturesDirectory() . '/' . Rds::API_VERSION_CURRENT . '/' . $filename;
    }

    /**
     * Gets Rds Mock
     *
     * @param    callback $callback
     * @return   Rds      Returns Rds Mock class
     */
    public function getRdsMock($callback = null)
    {
        return $this->getServiceInterfaceMock('Rds');
    }

    /**
     * @test
     */
    public function testDescribeDBInstances()
    {
        $rds = $this->getRdsMock();
        $dbInstanceList = $rds->dbInstance->describe();
        $this->assertInstanceOf($this->getRdsClassName('DataType\\DBInstanceList'), $dbInstanceList);
        $this->assertInstanceOf($this->getAwsClassName('Rds'), $dbInstanceList->getRds());
        $this->assertEquals(1, count($dbInstanceList));

        /* @var $dbi DBInstanceData */
        $dbi = $dbInstanceList->get(0);
        $this->assertInstanceOf($this->getRdsClassName('DataType\\DBInstanceData'), $dbi);
        $this->assertInstanceOf($this->getAwsClassName('Rds'), $dbi->getRds());
        $this->assertEquals('foo-rds', $dbi->dBInstanceIdentifier);
        $this->assertSame($dbi, $rds->dbInstance->get($dbi->dBInstanceIdentifier));

        $this->assertEquals(1, $dbi->backupRetentionPeriod);
        $this->assertEquals('available', $dbi->dBInstanceStatus);
        $this->assertEquals(false, $dbi->multiAZ);

        $this->assertInstanceOf($this->getRdsClassName('DataType\\VpcSecurityGroupMembershipList'), $dbi->vpcSecurityGroups);
        $this->assertInstanceOf($this->getAwsClassName('Rds'), $dbi->vpcSecurityGroups->getRds());
        $this->assertEquals(1, count($dbi->vpcSecurityGroups));
        /* @var $vpcgroup VpcSecurityGroupMembershipData */
        $vpcgroup = $dbi->vpcSecurityGroups->get(0);
        $this->assertInstanceOf($this->getRdsClassName('DataType\\VpcSecurityGroupMembershipData'), $vpcgroup);
        $this->assertInstanceOf($this->getAwsClassName('Rds'), $vpcgroup->getRds());
        $this->assertEquals('vpc-secuity-group-id', $vpcgroup->vpcSecurityGroupId);
        $this->assertEquals('vpc-status', $vpcgroup->status);
        unset($vpcgroup);

        $this->assertEquals('10:00-12:00', $dbi->preferredBackupWindow);
        $this->assertEquals('mon:05:00-mon:09:00', $dbi->preferredMaintenanceWindow);

        $this->assertInstanceOf($this->getRdsClassName('DataType\\OptionGroupMembershipData'), $dbi->optionGroupMembership);
        $this->assertInstanceOf($this->getAwsClassName('Rds'), $dbi->optionGroupMembership->getRds());
        $this->assertEquals('default:mysql-5-1', $dbi->optionGroupMembership->optionGroupName);
        $this->assertEquals('in-sync', $dbi->optionGroupMembership->status);

        $this->assertEquals('us-east-1a', $dbi->availabilityZone);
        $this->assertEquals('2013-03-19T16:15:00+00:00', $dbi->latestRestorableTime->format('c'));
        $this->assertEquals(array('replica-foo-rds'), $dbi->readReplicaDBInstanceIdentifiers);
        $this->assertEquals('mysql', $dbi->engine);

        $this->assertEquals(null, $dbi->pendingModifiedValues);

        $this->assertEquals('general-public-license', $dbi->licenseModel);

        $this->assertInstanceOf($this->getRdsClassName('DataType\\DBParameterGroupStatusList'), $dbi->dBParameterGroups);
        $this->assertInstanceOf($this->getAwsClassName('Rds'), $dbi->dBParameterGroups->getRds());
        $this->assertEquals(1, count($dbi->dBParameterGroups));
        /* @var $pargroup DBParameterGroupStatusData */
        $pargroup = $dbi->dBParameterGroups->get(0);
        $this->assertInstanceOf($this->getRdsClassName('DataType\\DBParameterGroupStatusData'), $pargroup);
        $this->assertInstanceOf($this->getAwsClassName('Rds'), $pargroup->getRds());
        $this->assertEquals('in-sync', $pargroup->parameterApplyStatus);
        $this->assertEquals('default.mysql5.1', $pargroup->dBParameterGroupName);
        unset($pargroup);

        $this->assertInstanceOf($this->getRdsClassName('DataType\\EndpointData'), $dbi->endpoint);
        $this->assertInstanceOf($this->getAwsClassName('Rds'), $dbi->endpoint->getRds());
        $this->assertEquals(3306, $dbi->endpoint->port);
        $this->assertEquals('foo-rds.c13pxxclnfjg.us-east-1.rds.amazonaws.com', $dbi->endpoint->address);

        $this->assertEquals('5.1.63', $dbi->engineVersion);
        $this->assertEquals(true, $dbi->publiclyAccessible);

        $this->assertInstanceOf($this->getRdsClassName('DataType\\DBSecurityGroupMembershipList'), $dbi->dBSecurityGroups);
        $this->assertInstanceOf($this->getAwsClassName('Rds'), $dbi->dBSecurityGroups->getRds());
        $this->assertEquals(1, count($dbi->dBSecurityGroups));
        /* @var $dbsg DBSecurityGroupMembershipData */
        $dbsg = $dbi->dBSecurityGroups->get(0);
        $this->assertInstanceOf($this->getRdsClassName('DataType\\DBSecurityGroupMembershipData'), $dbsg);
        $this->assertInstanceOf($this->getAwsClassName('Rds'), $dbsg->getRds());
        $this->assertEquals('default', $dbsg->dBSecurityGroupName);
        $this->assertEquals('active', $dbsg->status);
        unset($dbsg);

        $this->assertEquals(true, $dbi->autoMinorVersionUpgrade);
        $this->assertEquals('2012-12-09T21:47:08+00:00', $dbi->instanceCreateTime->format('c'));
        $this->assertEquals(5, $dbi->allocatedStorage);
        $this->assertEquals('db.m1.small', $dbi->dBInstanceClass);
        $this->assertEquals('root', $dbi->masterUsername);

        $rds->getEntityManager()->detachAll();
    }

    /**
     * @test
     */
    public function testDescribeDBSecurityGroups()
    {
        $rds = $this->getRdsMock();
        $dbsglist = $rds->dbSecurityGroup->describe();

        $this->assertInstanceOf($this->getRdsClassName('DataType\\DBSecurityGroupList'), $dbsglist);
        $this->assertInstanceOf($this->getAwsClassName('Rds'), $dbsglist->getRds());
        $this->assertEquals(3, count($dbsglist));

        /* @var $sg DBSecurityGroupData */
        $sg = $dbsglist->get(0);
        $this->assertInstanceOf($this->getRdsClassName('DataType\\DBSecurityGroupData'), $sg);
        $this->assertInstanceOf($this->getAwsClassName('Rds'), $sg->getRds());
        $this->assertEquals('default', $sg->dBSecurityGroupDescription);
        $this->assertEquals('default-name', $sg->dBSecurityGroupName);
        $this->assertEquals('621567473609', $sg->ownerId);
        $this->assertEquals('vpc-1ab2c3d4', $sg->vpcId);

        $this->assertInstanceOf($this->getRdsClassName('DataType\\IPRangeList'), $sg->iPRanges);
        $this->assertInstanceOf($this->getAwsClassName('Rds'), $sg->iPRanges->getRds());
        $this->assertEquals(1, count($sg->iPRanges));
        /* @var $iprange IPRangeData */
        $iprange = $sg->iPRanges->get(0);
        $this->assertInstanceOf($this->getRdsClassName('DataType\\IPRangeData'), $iprange);
        $this->assertInstanceOf($this->getAwsClassName('Rds'), $iprange->getRds());
        $this->assertEquals('127.0.0.1/30', $iprange->cIDRIP);
        $this->assertEquals('authorized', $iprange->status);
        unset($iprange);

        $this->assertInstanceOf($this->getRdsClassName('DataType\\EC2SecurityGroupList'), $sg->eC2SecurityGroups);
        $this->assertInstanceOf($this->getAwsClassName('Rds'), $sg->eC2SecurityGroups->getRds());
        $this->assertEquals(1, count($sg->eC2SecurityGroups));
        /* @var $ec2sg EC2SecurityGroupData */
        $ec2sg = $sg->eC2SecurityGroups->get(0);
        $this->assertInstanceOf($this->getRdsClassName('DataType\\EC2SecurityGroupData'), $ec2sg);
        $this->assertInstanceOf($this->getAwsClassName('Rds'), $ec2sg->getRds());
        $this->assertEquals(null, $ec2sg->eC2SecurityGroupId);
        $this->assertEquals('myec2securitygroup', $ec2sg->eC2SecurityGroupName);
        $this->assertEquals('054794666394', $ec2sg->eC2SecurityGroupOwnerId);
        $this->assertEquals('authorized', $ec2sg->status);
        unset($ec2sg);
        $rds->getEntityManager()->detachAll();
    }

    /**
     * @test
     */
    public function testDescribeDBSnapshots()
    {
        $rds = $this->getRdsMock();
        $snList = $rds->dbSnapshot->describe();

        $this->assertInstanceOf($this->getRdsClassName('DataType\\DBSnapshotList'), $snList);
        $this->assertInstanceOf($this->getAwsClassName('Rds'), $snList->getRds());
        $this->assertEquals(3, count($snList));
        /* @var $sn DBSnapshotData */
        $sn = $snList[0];
        $this->assertInstanceOf($this->getRdsClassName('DataType\\DBSnapshotData'), $sn);
        $this->assertInstanceOf($this->getAwsClassName('Rds'), $sn->getRds());
        $this->assertEquals(10, $sn->allocatedStorage);
        $this->assertEquals('us-east-1a', $sn->availabilityZone);
        $this->assertEquals('simcoprod01', $sn->dBInstanceIdentifier);
        $this->assertEquals('mydbsnapshot', $sn->dBSnapshotIdentifier);
        $this->assertEquals('mysql', $sn->engine);
        $this->assertEquals('5.1.50', $sn->engineVersion);
        $this->assertEquals('2011-05-23T06:06:43+00:00', $sn->instanceCreateTime->format('c'));
        $this->assertEquals(null, $sn->iops);
        $this->assertEquals('general-public-license', $sn->licenseModel);
        $this->assertEquals('master', $sn->masterUsername);
        $this->assertEquals(3306, $sn->port);
        $this->assertEquals('2011-05-23T06:29:03+00:00', $sn->snapshotCreateTime->format('c'));
        $this->assertEquals('manual', $sn->snapshotType);
        $this->assertEquals('available', $sn->status);
        $this->assertEquals(null, $sn->vpcId);

        $this->assertEquals(1000, $snList[1]->iops);
        $this->assertEquals('vpc-82983', $snList[1]->vpcId);

        $rds->getEntityManager()->detachAll();
    }

    /**
     * @test
     * @dataProvider providerClientType
     */
    public function testFunctionalRds($clientType)
    {
        $this->markTestSkipped();
        $this->skipIfEc2PlatformDisabled();

        $aws = $this->getContainer()->aws(AwsTestCase::REGION);
        $aws->rds->setApiClientType($clientType);
        $aws->rds->enableEntityManager();

        //Removes previously created DB Snapshots if it isn't removed by some reason.
        $snList = $aws->rds->dbSnapshot->describe();
        $this->assertInstanceOf($this->getRdsClassName('DataType\\DBSnapshotList'), $snList);
        foreach ($snList as $v) {
            if ($v->dbSnapshotIdentifier == self::getTestName(self::NAME_DB_SNAPSHOT)) {
                $v->delete();
                for ($to = 1, $t = time(); (time() - $t) < 600 && $v->status == DBSnapshotData::STATUS_DELETING; $to += 10) {
                    sleep($to);
                    try {
                        $v = $v->refresh();
                    } catch (ClientException $e) {
                        if ($e->getErrorData()->getCode() == ErrorData::ERR_DB_SNAPSHOT_NOT_FOUND) break;
                        throw $e;
                    }
                }
            }
        }
        unset($snList);

        //Removes previously created DB Instances if it isn't removed by some reason.
        $dbInstanceList = $aws->rds->dbInstance->describe();
        $this->assertInstanceOf($this->getRdsClassName('DataType\\DBInstanceList'), $dbInstanceList);
        /* @var $i DBInstanceData */
        foreach ($dbInstanceList as $i) {
            if ($i->dBInstanceIdentifier == self::getTestName(self::NAME_INSTANCE)) {
                if (!in_array($i->dBInstanceStatus, array(DBInstanceData::STATUS_DELETING))) {
                    $i = $i->delete(true);
                }
                for ($to = 1, $t = time(); (time() - $t) < 600 && $i->dBInstanceStatus == DBInstanceData::STATUS_DELETING; $to += 10) {
                    sleep($to);
                    try {
                        $i = $i->refresh();
                    } catch (ClientException $e) {
                        if ($e->getErrorData()->getCode() == ErrorData::ERR_DB_INSTANCE_NOT_FOUND) break;
                        throw $e;
                    }
                }
            }
        }

        //Removes previously created DB Security Group if it isn't removed by some reason.
        $sgList = $aws->rds->dbSecurityGroup->describe();
        $this->assertInstanceOf($this->getRdsClassName('DataType\\DBSecurityGroupList'), $sgList);
        /* @var $sg DBSecurityGroupData */
        foreach ($sgList as $sg) {
            if ($sg->dBSecurityGroupName == self::getTestName(self::NAME_SG)) {
                //DB Security Group must not be associated with any DBInstance
                $ret = $sg->delete();
                $this->assertTrue($ret);
            }
            unset($sg);
        }

        //Creates DB Instance
        $masterUserPassword = substr(uniqid(), 0, 10);
        $req = new CreateDBInstanceRequestData(
            self::getTestName(self::NAME_INSTANCE), 5, 'db.t1.micro', 'MySQL', 'masterusername', $masterUserPassword
        );
        $dbi = $aws->rds->dbInstance->create($req);
        unset($req);
        $this->assertInstanceOf($this->getRdsClassName('DataType\\DBInstanceData'), $dbi);
        for ($to = 1, $t = time(); (time() - $t) < 600 && $dbi->dBInstanceStatus != DBInstanceData::STATUS_AVAILABLE; $to += 10) {
            sleep($to);
            $dbi = $dbi->refresh();
        }
        $this->assertEquals(DBInstanceData::STATUS_AVAILABLE, $dbi->dBInstanceStatus);

        //Creates DB Security Group
        $sg = $aws->rds->dbSecurityGroup->create(self::getTestName('dsg'), 'phpunit temporary security group');
        $this->assertInstanceOf($this->getRdsClassName('DataType\\DBSecurityGroupData'), $sg);

        //Modifies DB Instance
        $req = $dbi->getModifyRequest();
        $req->masterUserPassword = substr(uniqid(), 0, 10);
        $req->dBSecurityGroups = $sg->dBSecurityGroupName;
        $dbi = $dbi->modify($req);
        $this->assertInstanceOf($this->getRdsClassName('DataType\\DBInstanceData'), $dbi);
        unset($req);

        //Reboots DB Instance
        $dbi = $dbi->reboot();
        $this->assertInstanceOf($this->getRdsClassName('DataType\\DBInstanceData'), $dbi);
        for ($to = 1, $t = time(); (time() - $t) < 600 && $dbi->dBInstanceStatus != DBInstanceData::STATUS_AVAILABLE; $to += 10) {
            sleep($to);
            $dbi = $dbi->refresh();
        }
        $this->assertEquals(DBInstanceData::STATUS_AVAILABLE, $dbi->dBInstanceStatus);

        //Created DB Snapshot
        $sn = $dbi->createSnapshot(self::getTestName(self::NAME_DB_SNAPSHOT));
        $this->assertInstanceOf($this->getRdsClassName('DataType\\DBSnapshotData'), $sn);
        for ($to = 1, $t = time(); (time() - $t) < 600 && $sn->status !== DBSnapshotData::STATUS_AVAILABLE; $to += 10) {
            sleep($to);
            $sn = $sn->refresh();
        }
        $this->assertEquals(DBSnapshotData::STATUS_AVAILABLE, $sn->status);

        //Removes DB Instance
        $dbi = $dbi->delete(true);
        $this->assertInstanceOf($this->getRdsClassName('DataType\\DBInstanceData'), $dbi);
        for ($to = 1, $t = time(); (time() - $t) < 600 && $dbi->dBInstanceStatus == DBInstanceData::STATUS_DELETING; $to += 10) {
            sleep($to);
            try {
                $dbi = $dbi->refresh();
            } catch (ClientException $e) {
                if ($e->getErrorData()->getCode() == ErrorData::ERR_DB_INSTANCE_NOT_FOUND) break;
                throw $e;
            }
        }
        unset($dbi);

        //Restores DB Instance From DB Snapshot
        $dbi = $sn->restoreFromSnapshot(self::getTestName(self::NAME_INSTANCE));
        $this->assertInstanceOf($this->getRdsClassName('DataType\\DBInstanceData'), $dbi);
        for ($to = 1, $t = time(); (time() - $t) < 600 && $dbi->dBInstanceStatus !== DBInstanceData::STATUS_AVAILABLE; $to += 10) {
            sleep($to);
            $dbi = $dbi->refresh();
        }
        $this->assertEquals(DBInstanceData::STATUS_AVAILABLE, $dbi->dBInstanceStatus);

        //Removes DB Snapshot
        $sn = $sn->delete();
        for ($to = 1, $t = time(); (time() - $t) < 600 && $sn->status == DBSnapshotData::STATUS_DELETING; $to += 10) {
            sleep($to);
            try {
                $sn = $sn->refresh();
            } catch (ClientException $e) {
                if ($e->getErrorData()->getCode() == ErrorData::ERR_DB_SNAPSHOT_NOT_FOUND) break;
                throw $e;
            }
        }
        unset($sn);

        //Removes DB Instance again
        $dbi = $dbi->delete(true);
        $this->assertInstanceOf($this->getRdsClassName('DataType\\DBInstanceData'), $dbi);
        for ($to = 1, $t = time(); (time() - $t) < 600 && $dbi->dBInstanceStatus == DBInstanceData::STATUS_DELETING; $to += 10) {
            sleep($to);
            try {
                $dbi = $dbi->refresh();
            } catch (ClientException $e) {
                if ($e->getErrorData()->getCode() == ErrorData::ERR_DB_INSTANCE_NOT_FOUND) break;
                throw $e;
            }
        }
        unset($dbi);

        //Removes DB Security Group
        $ret = $sg->delete();
        $this->assertTrue($ret);

        $aws->rds->getEntityManager()->detachAll();
    }

    /**
     * @test
     * @dataProvider providerClientType
     */
    public function testFunctionalFast($clientType)
    {
        $this->markTestSkipped();
        $this->skipIfEc2PlatformDisabled();

        $aws = $this->getContainer()->aws(AwsTestCase::REGION);
        $aws->rds->setApiClientType($clientType);
        $aws->rds->enableEntityManager();

        $req = new DescribeEventRequestData();
        $req->startTime = new \DateTime('-2 hour', new \DateTimeZone('UTC'));
        $req->eventCategories = array('deletion', 'availability');
        $eventList = $aws->rds->event->describe($req);
        $this->assertInstanceOf($this->getRdsClassName('DataType\\EventList'), $eventList);

        //Describes DB Parameters
        $parList = $aws->rds->dbParameterGroup->describeParameters('default.mysql5.5');
        $this->assertInstanceOf($this->getRdsClassName('DataType\\ParameterList'), $parList);
        unset($parList);

        //Describes DB Parameter Groups
        $dbParameterGroupList = $aws->rds->dbParameterGroup->describe();
        $this->assertInstanceOf($this->getRdsClassName('DataType\\DBParameterGroupList'), $dbParameterGroupList);
        foreach ($dbParameterGroupList as $v) {
            //Removes previously created DB Parameter Group if it does exist
            if ($v->dBParameterGroupName == self::getTestName(self::NAME_DB_PARAMETER_GROUP)) {
                $v->delete();
            }
        }
        unset($dbParameterGroupList);

        //Creates a new DBParameterGroup
        $dbParameterGroup = $aws->rds->dbParameterGroup->create(new DBParameterGroupData(
            self::getTestName(self::NAME_DB_PARAMETER_GROUP), 'mysql5.5', 'phpunit temporary group'
        ));
        $this->assertInstanceOf($this->getRdsClassName('DataType\\DBParameterGroupData'), $dbParameterGroup);
        $this->assertEquals(self::getTestName(self::NAME_DB_PARAMETER_GROUP), $dbParameterGroup->dBParameterGroupName);
        $this->assertEquals('mysql5.5', $dbParameterGroup->dBParameterGroupFamily);
        $this->assertEquals('phpunit temporary group', $dbParameterGroup->description);
        $this->assertSame($dbParameterGroup, $aws->rds->dbParameterGroup->get(self::getTestName(self::NAME_DB_PARAMETER_GROUP)));

        //Modifies parameters
        $ret = $dbParameterGroup->modify(array(
            new ParameterData('autocommit', ParameterData::APPLY_METHOD_PENDING_REBOOT, '0'),
            new ParameterData('automatic_sp_privileges', ParameterData::APPLY_METHOD_PENDING_REBOOT, '0'),
        ));
        $this->assertEquals($dbParameterGroup->dBParameterGroupName, $ret);

        //Resets parameters
        $ret = $dbParameterGroup->reset(array(
            new ParameterData('auto_increment_offset', ParameterData::APPLY_METHOD_PENDING_REBOOT),
        ));
        $this->assertEquals($dbParameterGroup->dBParameterGroupName, $ret);

        //Removes DBParameterGroup
        $ret = $dbParameterGroup->delete();
        $this->assertTrue($ret);

        //Removes previously created DB Security Group if it isn't removed by some reason.
        $sgList = $aws->rds->dbSecurityGroup->describe();
        $this->assertInstanceOf($this->getRdsClassName('DataType\\DBSecurityGroupList'), $sgList);
        /* @var $sg DBSecurityGroupData */
        foreach ($sgList as $sg) {
            if ($sg->dBSecurityGroupName == self::getTestName(self::NAME_SG)) {
                //DB Security Group must not be associated with any DBInstance
                $ret = $sg->delete();
                $this->assertTrue($ret);
            }
            unset($sg);
        }
        unset($sgList);

        $sg = $aws->rds->dbSecurityGroup->create(self::getTestName(self::NAME_SG), 'phpunit temporary security group');
        $this->assertInstanceOf($this->getRdsClassName('DataType\\DBSecurityGroupData'), $sg);

        $req = $sg->getIngressRequest();
        $req->cIDRIP = '0.0.0.1/0';
        $sg2 = $sg->authorizeIngress($req);
        $this->assertSame($sg2, $sg);
        unset($sg2);
        $this->assertInstanceOf($this->getRdsClassName('DataType\\DBSecurityGroupData'), $sg);
        $this->assertEquals('0.0.0.1/0', $sg->iPRanges->get(0)->cIDRIP);

        //Avoids an error - cannot revoke an authorization which is in the authorizing state
        for ($to = 1, $t = time(); (time() - $t) < 600 && count($sg->iPRanges); $to += 10) {
            foreach ($sg->iPRanges as $r) {
                if ($r->status == IPRangeData::STATUS_AUTHORIZED) {
                    break 2;
                }
            }
            sleep($to);
            $sg = $sg->refresh();
        }

        $sg2 = $sg->revokeIngress($req);
        $this->assertSame($sg2, $sg);
        $this->assertInstanceOf($this->getRdsClassName('DataType\\DBSecurityGroupData'), $sg);
        unset($sg2);

        $timeout = 1;
        while (count($sg->iPRanges) && ($timeout += 10) < 600) {
            sleep($timeout);
            $sg = $sg->refresh();
        }

        $this->assertEquals(0, count($sg->iPRanges));

        $ret = $sg->delete();
        $this->assertTrue($ret);
        $aws->rds->getEntityManager()->detachAll();
    }
}