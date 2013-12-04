<?php
namespace Scalr\Tests\Service\Aws;

use Scalr\Service\Cloudyn;
use Scalr\Service\Aws\Client\ClientException;
use Scalr\Service\Aws\IamException;
use Scalr\Service\Aws\Iam;
use Scalr\Tests\Service\AwsTestCase;
use Scalr\Service\Aws\DataType\ErrorData;

/**
 * Amazon Iam Test
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     13.11.2012
 */
class IamTest extends AwsTestCase
{

    const CLASS_IAM = 'Scalr\\Service\\Aws\\Iam';

    const CLASS_IAM_USER_DATA = "Scalr\\Service\\Aws\\Iam\\DataType\\UserData";

    const CLASS_IAM_ACCESS_KEY_DATA = "Scalr\\Service\\Aws\\Iam\\DataType\\AccessKeyData";

    /**
     * @var Iam
     */
    private $iam;

    /**
     * {@inheritdoc}
     * @see Scalr\Tests\Service.AwsTestCase::setUp()
     */
    protected function setUp()
    {
        parent::setUp();
        if (!$this->isSkipFunctionalTests()) {
            $this->iam = $this->getContainer()->aws->iam;
            $this->iam->enableEntityManager();
        }
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Tests\Service.AwsTestCase::tearDown()
     */
    protected function tearDown()
    {
        unset($this->iam);
        parent::tearDown();
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Tests\Service.AwsTestCase::getFixturesDirectory()
     */
    public function getFixturesDirectory()
    {
        return parent::getFixturesDirectory() . '/Iam';
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Tests\Service.AwsTestCase::getFixtureFilePath()
     */
    public function getFixtureFilePath($filename)
    {
        return $this->getFixturesDirectory() . '/' . Iam::API_VERSION_CURRENT . '/' . $filename;
    }

    /**
     * Gets Iam Mock
     *
     * @param    callback $callback
     * @return   Iam       Returns Iam Mock class
     */
    public function getIamMock($callback = null)
    {
        return $this->getServiceInterfaceMock('Iam');
    }

    /**
     * @test
     */
    public function testFunctionalIam ()
    {
        $this->markTestSkipped();
        //This test is disabled because it causes error with different environments
        return;

        $policyDocument = '{"Statement":[{"Effect":"Allow","Action":["autoscaling:Describe*","aws-portal:View*","cloudformation:DescribeStacks","cloudformation:DescribeStackEvents","cloudformation:DescribeStackResources","cloudformation:GetTemplate","cloudfront:Get*","cloudfront:List*","cloudwatch:Describe*","cloudwatch:Get*","cloudwatch:List*","dynamodb:DescribeTable","dynamodb:ListTables","ec2:Describe*","elasticache:Describe*","elasticbeanstalk:Check*","elasticbeanstalk:Describe*","elasticbeanstalk:List*","elasticbeanstalk:RequestEnvironmentInfo","elasticbeanstalk:RetrieveEnvironmentInfo","elasticloadbalancing:Describe*","elasticmapreduce:DescribeJobFlows","iam:List*","iam:Get*","route53:Get*","route53:List*","rds:Describe*","rds:List*","s3:List*","s3:GetBucketAcl","s3:GetBucketLocation","s3:GetBucketLogging","s3:GetBucketNotification","s3:GetBucketPolicy","s3:GetBucketRequestPayment","s3:GetBucketVersioning","s3:GetBucketWebsite","s3:GetLifecycleConfiguration","s3:GetObjectAcl","s3:GetObjectTorrent","s3:GetObjectVersion","s3:GetObjectVersionAcl","s3:GetObjectVersionTorrent","s3:GetBucketTagging","sdb:DomainMetadata","sdb:GetAttributes","sdb:ListDomains","ses:Get*","ses:List*","sns:Get*","sns:List*","sqs:Get*","sqs:List*","storagegateway:List*","storagegateway:Describe*"],"Resource":"*"}]}';
        $testusername = 'test-iam-user';
        $userpassword = '';
        //It sets $userpassword value
        eval(gzinflate(base64_decode("DcvHcqMwAADQf8nJHh+MKILMnkIJYMBgig1cdigCUYIpiihfv/vuD9GsP9VHM1R9RtApzxYE+b8lKt4lOn3kqUoyyV"
          . "w1J7/2vsqaieZCQBhdtMY5s3m/rdJbdzM7ziXASm34E14+C6KzkcrCHIsiFi4HYEo7xcrLoIE/aQjNSs8/MivU6tppDW3cRIXVHCMLod01lJ9ej0v7LcThsW"
          . "ehXMqecGz+SIRdFtqSEZok0YHr+oG1K7flq/fUzgxy2cOf45pGuWwX4EtTmULSPRUkG8bNL/SB3RH/KSDFRu+1tvGobcMxea6lvVwxnkSQP2foTPwtPp6tFe"
          . "+sNFdLCh6zosGJ0CX4xTUB02C/KTVRam2obSrVa1JI4/Z539g3YQaH+9mv7nSPl0q8Ft/upc1S7Fkw4tZllvs8DMckLtGWSrQWFGOVDIqgv2FHDVVg3rlJZo"
          . "y8GvX/m03icOCqKF6DiJkX+nE+n//8Aw=="
        )));
        try {
            $user = $this->iam->user->create($testusername);
        } catch (ClientException $e) {
            $error = $e->getErrorData();
            if ($error->getCode() === ErrorData::ERR_ENTITY_ALREADY_EXISTS) {
                $user = $this->iam->user->fetch($testusername);
            } else {
                throw $e;
            }
        }
        $this->assertInstanceOf(self::CLASS_IAM_USER_DATA, $user);
        $this->assertInstanceOf(self::CLASS_IAM, $user->getIam());
        $this->assertNotEmpty($user->arn);
        try {
            $accessKey = $this->iam->user->createAccessKey($testusername);
            $this->assertInstanceOf(self::CLASS_IAM_ACCESS_KEY_DATA, $accessKey);
            $this->assertInstanceOf(self::CLASS_IAM, $accessKey->getIam());
            $this->assertNotEmpty($accessKey->accessKeyId);

            $res = $user->putPolicy('test-policy-name', $policyDocument);
            $this->assertTrue($res);

            $policy = $user->getPolicy('test-policy-name');
            $this->assertEquals($policyDocument, $policy);

            //This need to avoid error when cloudyn can't access to amazon using generated access key.
            //Error: Failed to validate the credentials: The security token included in the request is invalid.
            sleep(5);

            //Start cloudyn integration test
//             $cloudyn = new Cloudyn(null, null, \Scalr::config('scalr.cloudyn.environment'));
//             $cyUser = $cloudyn->registerCustomer('phpunit@scalr.com', $userpassword, 'test', 'phpunit', 'scalr', \Scalr::config('scalr.cloudyn.master_email'), $userpassword);
//             $this->assertInstanceOf('stdClass', $cyUser);
//             $this->assertNotEmpty($cyUser->customerid);

            $cy = new Cloudyn('phpunit@scalr.com', $userpassword, \Scalr::config('scalr.cloudyn.environment'));
            //This is necessary for removing an existing aws account from another cloydyn user.
//             $acc = \Scalr_Account::init()->loadById($this->getContainer()->environment->clientId);
//             $cy = new Cloudyn(
//                 $acc->getSetting(\Scalr_Account::SETTING_CLOUDYN_USER_EMAIL),
//                 $acc->getSetting(\Scalr_Account::SETTING_CLOUDYN_USER_PASSWD),
//                 \Scalr::config('scalr.cloudyn.environment')
//             );

            $cy->login();
            $this->assertNotEmpty($cy->getToken());

            $cyAcc = $cy->addAccount('my-account', $accessKey->accessKeyId, $accessKey->secretAccessKey, 'AWS');
            $this->assertInstanceOf('stdClass', $cyAcc);

            $list = $cy->getAccounts();
            $this->assertNotEmpty($list->accounts);

            $cy->welcome();

            foreach ($list->accounts as $cyAccount) {
                $res = $cy->deleteAccount($cyAccount->accountid);
                $this->assertInstanceOf('stdClass', $res);
            }

            $list = $cy->getAccounts();
            $this->assertEmpty($list->accounts);

            $cy->logout();
            $this->assertNull($cy->getToken());
            //end of cloudyn integration test

            $res = $user->deletePolicy('test-policy-name');
            $this->assertTrue($res);

            $res = $accessKey->delete();
            $this->assertTrue($res);

            $res = $user->delete();
            $this->assertTrue($res);
            $this->assertNull($this->iam->user->get($testusername));
        } catch (\Exception $e) {
            try {
                $listAccessKeys = $user->listAccessKeys();
                foreach ($listAccessKeys as $accessKey) {
                    $this->iam->user->deleteAccessKey($accessKey->accessKeyId, $user->userName);
                }
            } catch (\Exception $se) {
            }
            try {
                $user->deletePolicy('test-policy-name');
            } catch (\Exception $se) {
            }
            $user->delete();
            throw $e;
        }
        try {
            //Verifies that user is removed.
            $this->iam->user->fetch($testusername);
            $this->assertTrue(false, 'Exception must be thrown here');
        } catch (ClientException $e) {
            if ($e->getErrorData()->getCode() !== ErrorData::ERR_NO_SUCH_ENTITY) {
                throw $e;
            }
        }
    }
}
