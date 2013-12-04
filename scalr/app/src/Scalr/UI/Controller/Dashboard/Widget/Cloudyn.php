<?php
use Scalr\Service\Cloudyn;
use Scalr\Service\CloudynException;
use Scalr\Service\Aws\DataType\ErrorData;
use Scalr\Service\Aws\Client\ClientException;

class Scalr_UI_Controller_Dashboard_Widget_Cloudyn extends Scalr_UI_Controller_Dashboard_Widget
{
    /**
     * {@inheritdoc}
     * @see Scalr_UI_Controller_Dashboard_Widget::hasAccess()
     */
    public function hasAccess()
    {
        if (!\Scalr::config('scalr.cloudyn.master_email')) {
            throw new Scalr_Exception_Core('Cloudyn integration is not enabled');
        }

        return parent::hasAccess();
    }

    public function getDefinition()
    {
        return array(
            'type' => 'nonlocal'
        );
    }

    /**
     * {@inheritdoc}
     * @see Scalr_UI_Controller_Dashboard_Widget::getContent()
     */
    public function getContent($params = array())
    {
        $env = $this->getEnvironment();
        $container = $env->getContainer();
        //Gets Scalr_Session instance
        $session = $container->session;

        $owner = $this->user->getType() == Scalr_Account_User::TYPE_ACCOUNT_OWNER ||
            $this->user->isTeamUserInEnvironment($this->getEnvironmentId(), Scalr_Account_Team::PERMISSIONS_OWNER);

        if ($env->getPlatformConfigValue(ENVIRONMENT_SETTINGS::CLOUDYN_ENABLED)) {
            //Gets a Cloudyn instance
            $cy = $container->cloudyn;
            //Tries to get cloudyn token which is stored in session
            $token = $session->getCloudynToken();
            if (!empty($token)) {
                $cy->setToken($token);
                try {
                    //Tries to validate a cloudyn session
                    $obj = $cy->validateSession();
                } catch (CloudynException $e) {
                    //Invalid session
                    $obj = null;
                }
                if (!isset($obj->status) || $obj->status !== 'ok') {
                    $bShouldLogin = true;
                }
            } else {
                $bShouldLogin = true;
            }

            if (isset($bShouldLogin)) {
                //If cloudyn session is invalid it will create new session
                $cy->login();
                //Stores new Cloudyn Token in Scalr session
                $session->setCloudynToken($cy->getToken());
            }
            $metrics = $cy->welcome($env->getPlatformConfigValue(ENVIRONMENT_SETTINGS::CLOUDYN_ACCOUNTID));
            $accountStatistics = isset($metrics->elements[0]->content->compound) ? $metrics->elements[0]->content->compound : null;
            if ($accountStatistics !== null) {
                foreach ($accountStatistics as $k => $v) {
                    if ($v->CompletionDate instanceof DateTime) {
                        $v->CompletionDateTz = Scalr_Util_DateTime::convertTz($v->CompletionDate->getTimestamp());
                        $diff = $v->CompletionDate->getTimestamp() - time();
                        if ($diff > 120) {
                            if ($diff < 600) {
                                $t = ceil($diff / 60);
                                $v->estimate = sprintf('in %d minute' . ($t > 1 ? 's' : ''), $t);
                            } else if ($diff < 3600) {
                                $v->estimate = 'in less than an hour';
                            } else if ($diff < 21600) {
                                $t = round($diff / 3600);
                                $v->estimate = sprintf('in %d hour' . ($t > 1 ? 's' : ''), $t);
                            } else {
                                $v->estimate = 'in more than 6 hours';
                            }
                        }
                    }
                }
            }

            //TODO for Invar: Show error on widget "Cannot get statistics due to internal Cloudyn error." if $accountStatistics is null

            return array('enabled' => true, 'owner' => $owner, 'metrics' => $accountStatistics, 'consoleUrl' => $cy->getConsoleUrl());
        } else {
            return array('enabled' => false, 'owner' => $owner);
        }
    }

    /**
     * Removes AWS Account from Cloudyn
     */
    public function xRemoveCloudynAction()
    {
        if (! ($this->user->getType() == Scalr_Account_User::TYPE_ACCOUNT_OWNER ||
            $this->user->isTeamUserInEnvironment($this->getEnvironmentId(), Scalr_Account_Team::PERMISSIONS_OWNER)))
            throw new Scalr_Exception_InsufficientPermissions();

        $env = $this->getEnvironment();
        $isCloudynEnvironmentEnabled = $env->getPlatformConfigValue(ENVIRONMENT_SETTINGS::CLOUDYN_ENABLED);
        if ($isCloudynEnvironmentEnabled) {
            $cy = $env->cloudyn;
            $cy->login();
            $list = $cy->getAccounts();
            foreach ($list->accounts as $cyAccount) {
                $cy->deleteAccount($cyAccount->accountid);
            }
            $env->setPlatformConfig(array(
                ENVIRONMENT_SETTINGS::CLOUDYN_ENABLED       => 0,
                ENVIRONMENT_SETTINGS::CLOUDYN_AWS_ACCESSKEY => null,
                ENVIRONMENT_SETTINGS::CLOUDYN_ACCOUNTID     => null,
            ));
            $cy->logout();
        }
        $this->response->data(array('result' => true));
    }

    public function xSetupAction()
    {
        if (! ($this->user->getType() == Scalr_Account_User::TYPE_ACCOUNT_OWNER ||
            $this->user->isTeamUserInEnvironment($this->getEnvironmentId(), Scalr_Account_Team::PERMISSIONS_OWNER)))
            throw new Scalr_Exception_InsufficientPermissions();

        $env = $this->getEnvironment();
        $acc = $this->user->getAccount();
        $iam = $env->aws->iam;

        //Generates both master and user passwords
        $masterEmail = \Scalr::config('scalr.cloudyn.master_email');
        $userEmail = $acc->getOwner()->getEmail();

        $masterPassword = $this->getCrypto()->sault(8);
        $userPassword = $this->getCrypto()->sault(8);
        //Gets an user name using naming conventions
        $awsUsername = sprintf('scalr-cloudyn-%s-%s', $env->id, SCALR_ID);
        $policyName = sprintf('cloudynpolicy-%s', $env->id);
        $cyAccountName = sprintf('scalr-aws-account-%s', $env->id);
        //Read-only AWS policy
        $policyDocument = '{"Statement":[{"Effect":"Allow","Action":["autoscaling:Describe*","aws-portal:View*","cloudformation:DescribeStacks","cloudformation:DescribeStackEvents","cloudformation:DescribeStackResources","cloudformation:GetTemplate","cloudfront:Get*","cloudfront:List*","cloudwatch:Describe*","cloudwatch:Get*","cloudwatch:List*","dynamodb:DescribeTable","dynamodb:ListTables","ec2:Describe*","elasticache:Describe*","elasticbeanstalk:Check*","elasticbeanstalk:Describe*","elasticbeanstalk:List*","elasticbeanstalk:RequestEnvironmentInfo","elasticbeanstalk:RetrieveEnvironmentInfo","elasticloadbalancing:Describe*","elasticmapreduce:DescribeJobFlows","iam:List*","iam:Get*","route53:Get*","route53:List*","rds:Describe*","rds:List*","s3:List*","s3:GetBucketAcl","s3:GetBucketLocation","s3:GetBucketLogging","s3:GetBucketNotification","s3:GetBucketPolicy","s3:GetBucketRequestPayment","s3:GetBucketVersioning","s3:GetBucketWebsite","s3:GetLifecycleConfiguration","s3:GetObjectAcl","s3:GetObjectTorrent","s3:GetObjectVersion","s3:GetObjectVersionAcl","s3:GetObjectVersionTorrent","s3:GetBucketTagging","sdb:DomainMetadata","sdb:GetAttributes","sdb:ListDomains","ses:Get*","ses:List*","sns:Get*","sns:List*","sqs:Get*","sqs:List*","storagegateway:List*","storagegateway:Describe*"],"Resource":"*"}]}';

        $isCloudynEnabled = $acc->getSetting(Scalr_Account::SETTING_CLOUDYN_ENABLED);
        $isCloudynEnvironmentEnabled = $env->getPlatformConfigValue(ENVIRONMENT_SETTINGS::CLOUDYN_ENABLED);

        if ($isCloudynEnvironmentEnabled) {
            throw new RuntimeException('Cloudyn account for this environment has already been enabled.');
        }

        //Creates a new AWS user using IAM
        try {
            $awsUser = $iam->user->create($awsUsername);
        }  catch (ClientException $e) {
            $error = $e->getErrorData();
            if ($error->getCode() === ErrorData::ERR_ENTITY_ALREADY_EXISTS) {
                $awsUser = $iam->user->fetch($awsUsername);
                try {
                    foreach ($awsUser->listAccessKeys() as $v) {
                        $iam->user->deleteAccessKey($v->accessKeyId, $awsUser->userName);
                    }
                } catch (\Exception $se) {
                }
                try {
                    $awsUser->deletePolicy($policyName);
                } catch (\Exception $se) {
                }
            } else {
                throw $e;
            }
        }

        //Adds polity to created user
        $awsUser->putPolicy($policyName, $policyDocument);
        //Generates new access key for the created user
        $accessKeyData = $awsUser->createAccessKey();

        //This need to avoid error when cloudyn can't access to amazon using generated access key.
        //Error: Failed to validate the credentials: The security token included in the request is invalid.
        sleep(8);

        //Whether cloudyn is enabled for this scalr account.
        //If not we must register new customer on Cloudyn.
        if (!$isCloudynEnabled) {
            //Initializes Cloudyn instance using generated user's credentials
            $cy = new Cloudyn($userEmail, $userPassword, \Scalr::config('scalr.cloudyn.environment'));
            $tokens = preg_split("/ +/", trim($this->user->fullname), 2);
            $userFirstName = !empty($tokens[0]) ? $tokens[0] : 'Unknown';
            $userLastName = isset($tokens[1]) ? $tokens[1] : 'Unknown';
            //Register new Customer on Cloudyn
            $cy->registerCustomer(
                $userEmail, $userPassword, $userFirstName, $userLastName,
                $acc->name, $masterEmail, $masterPassword
            );
            $acc
                ->setSetting(Scalr_Account::SETTING_CLOUDYN_ENABLED, 1)
                ->setSetting(Scalr_Account::SETTING_CLOUDYN_MASTER_EMAIL, $masterEmail)
                ->setSetting(Scalr_Account::SETTING_CLOUDYN_MASTER_PASSWD, $masterPassword)
                ->setSetting(Scalr_Account::SETTING_CLOUDYN_USER_EMAIL, $userEmail)
                ->setSetting(Scalr_Account::SETTING_CLOUDYN_USER_PASSWD, $userPassword)
            ;
        } else {
            //Initializes Cloudyn instance using existing user's credentials
            $cy = $env->cloudyn;
            //We doesn't need to register Customer as he has already registered.
        }
        //Login to cloudyn as a created user
        $cy->login();
        //Adds AWS account to cloudyn for the specified environment
        $result = $cy->addAccount($cyAccountName, $accessKeyData->accessKeyId, $accessKeyData->secretAccessKey, 'AWS');
        $cloudynAccountId = $result->accountid;

        $env->setPlatformConfig(array(
            ENVIRONMENT_SETTINGS::CLOUDYN_ENABLED       => 1,
            ENVIRONMENT_SETTINGS::CLOUDYN_AWS_ACCESSKEY => $accessKeyData->accessKeyId,
            ENVIRONMENT_SETTINGS::CLOUDYN_ACCOUNTID     => $cloudynAccountId,
        ));

        //Logout Cloudyn
        $cy->logout();

        $this->response->success('Your account successfully connected to Cloudyn');
        $this->response->data($this->getContent());
    }
}