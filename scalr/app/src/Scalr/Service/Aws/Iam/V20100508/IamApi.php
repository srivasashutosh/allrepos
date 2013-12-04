<?php
namespace Scalr\Service\Aws\Iam\V20100508;

use Scalr\Service\Aws\AbstractApi;
use Scalr\Service\Aws\Iam\DataType\AccessKeyMetadataData;
use Scalr\Service\Aws\Iam\DataType\AccessKeyMetadataList;
use Scalr\Service\Aws\Iam\DataType\AccessKeyData;
use Scalr\Service\Aws\Iam\DataType\UserData;
use Scalr\Service\Aws\Client\QueryClient;
use Scalr\Service\Aws\IamException;
use Scalr\Service\Aws\Iam;
use Scalr\Service\Aws\Client\ClientException;
use Scalr\Service\Aws\EntityManager;
use Scalr\Service\Aws\DataType\ErrorData;
use Scalr\Service\Aws\Client\QueryClientResponse;
use Scalr\Service\Aws\DataType\ListDataType;
use Scalr\Service\Aws\Client\ClientInterface;

/**
 * Iam Api messaging.
 *
 * Implements Iam Low-Level API Actions.
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     13.11.2012
 */
class IamApi extends AbstractApi
{

    /**
     * @var Iam
     */
    protected $iam;

    /**
     * Constructor
     *
     * @param   Iam                $iam           An Iam instance
     * @param   ClientInterface    $client        Client Interface
     */
    public function __construct (Iam $iam, ClientInterface $client)
    {
        $this->iam = $iam;
        $this->client = $client;
    }

    /**
     * PutUserPolicy action
     *
     * Adds (or updates) a policy document associated with the specified user.
     *
     * @param   string     $userName       Name of the user to associate the policy with.
     *                                     Length constraints: Minimum length of 1. Maximum length of 128.
     * @param   string     $policyName     Name of the policy document.
     *                                     Length constraints: Minimum length of 1. Maximum length of 128.
     * @param   string     $policyDocument The policy document.
     *                                     Length constraints: Minimum length of 1. Maximum length of 131072.
     * @return  bool       Returns true if policy is added (or updated)
     * @throws  IamException
     * @throws  ClientException
     */
    public function putUserPolicy ($userName, $policyName, $policyDocument)
    {
        $result = false;
        $options = array(
            'UserName'       => (string) $userName,
            'PolicyName'     => (string) $policyName,
            'PolicyDocument' => (string) $policyDocument,
        );
        $response = $this->client->call('PutUserPolicy', $options);
        if ($response->getError() === false) {
            //Success
            $sxml = simplexml_load_string($response->getRawContent());
            if (!isset($sxml->ResponseMetadata)) {
                throw new IamException('Unexpected response! ' . $response->getRawContent());
            }
            $result = true;
        }
        return $result;
    }

    /**
     * DeleteUserPolicy action
     *
     * @param   string     $userName       Name of the user to associate the policy with.
     *                                     Length constraints: Minimum length of 1. Maximum length of 128.
     * @param   string     $policyName     Name of the policy document.
     *                                     Length constraints: Minimum length of 1. Maximum length of 128.
     * @return  bool       Returns true if policy is successfully removed.
     * @throws  IamException
     * @throws  ClientException
     */
    public function deleteUserPolicy ($userName, $policyName)
    {
        $result = false;
        $options = array(
            'UserName'       => (string) $userName,
            'PolicyName'     => (string) $policyName,
        );
        $response = $this->client->call('DeleteUserPolicy', $options);
        if ($response->getError() === false) {
            //Success
            $sxml = simplexml_load_string($response->getRawContent());
            if (!isset($sxml->ResponseMetadata)) {
                throw new IamException('Unexpected response! ' . $response->getRawContent());
            }
            $result = true;
        }
        return $result;
    }

    /**
     * GetUserPolicy action
     *
     * Retrieves the specified policy document for the specified user.
     *
     * @param   string     $userName       Name of the user to associate the policy with.
     *                                     Length constraints: Minimum length of 1. Maximum length of 128.
     * @param   string     $policyName     Name of the policy document.
     *                                     Length constraints: Minimum length of 1. Maximum length of 128.
     * @return  string     Returns policy document
     * @throws  IamException
     * @throws  ClientException
     */
    public function getUserPolicy ($userName, $policyName)
    {
        $result = null;
        $options = array(
            'UserName'       => (string) $userName,
            'PolicyName'     => (string) $policyName,
        );
        $response = $this->client->call('GetUserPolicy', $options);
        if ($response->getError() === false) {
            //Success
            $sxml = simplexml_load_string($response->getRawContent());
            if (!isset($sxml->GetUserPolicyResult)) {
                throw new IamException('Unexpected response! ' . $response->getRawContent());
            }
            $result = rawurldecode((string) $sxml->GetUserPolicyResult->PolicyDocument);
        }
        return $result;
    }

    /**
     * CreateUser action
     *
     * Creates a new user for your AWS account.
     *
     * @param   string     $userName Name of the user to create.
     *                               Length constraints: Minimum length of 1. Maximum length of 512.
     * @param   string     $path     optional  The path for the user name.
     *                               Length constraints: Minimum length of 1. Maximum length of 64.
     * @return  UserData   Returns Information about the user.
     * @throws  IamException
     * @throws  ClientException
     */
    public function createUser ($userName, $path = null)
    {
        $result = null;
        $options = array(
            'UserName' => (string) $userName,
        );
        if ($path !== null) {
            $options['Path'] = (string) $path;
        }
        $response = $this->client->call('CreateUser', $options);
        if ($response->getError() === false) {
            //Success
            $sxml = simplexml_load_string($response->getRawContent());
            if (!isset($sxml->CreateUserResult)) {
                throw new IamException('Unexpected response! ' . $response->getRawContent());
            }
            $result = $this->_loadUserData($sxml->CreateUserResult->User);
            $this->getEntityManager()->attach($result);
        }
        return $result;
    }

    /**
     * Loads userdata from simple xml source
     *
     * @param   \SimpleXMLElement $sxml
     * @return  UserData Returns new user data object
     */
    private function _loadUserData (\SimpleXMLElement &$sxml)
    {
        $userData = new UserData();
        $userData->setIam($this->iam);
        $userData
            ->setPath((string)$sxml->Path)
            ->setUserName((string)$sxml->UserName)
            ->setUserId((string)$sxml->UserId)
            ->setArn((string)$sxml->Arn)
            ->setCreateDate(
                isset($sxml->CreateDate) ?
                new \DateTime((string)$sxml->CreateDate, new \DateTimeZone('UTC')) :
                new \DateTime(null, new \DateTimeZone('UTC'))
            )
        ;
        return $userData;
    }

    /**
     * GetUser action
     *
     * Retrieves information about the specified user, including the user's path, GUID, and ARN.
     * If you do not specify a user name, IAM determines the user name implicitly based on the
     * AWS Access Key ID signing the request.
     *
     * @param   string     $userName optional Name of the user to get information about.
     * @return  UserData   Returns Information about the user.
     * @throws  IamException
     * @throws  ClientException
     */
    public function getUser ($userName = null)
    {
        $result = null;
        $options = array();
        if ($userName !== null) {
            $options['UserName'] = (string) $userName;
        }
        $response = $this->client->call('GetUser', $options);
        if ($response->getError() === false) {
            //Success
            $sxml = simplexml_load_string($response->getRawContent());
            if (!isset($sxml->GetUserResult)) {
                throw new IamException('Unexpected response! ' . $response->getRawContent());
            }
            $result = $this->_loadUserData($sxml->GetUserResult->User);
            $this->getEntityManager()->attach($result);
        }
        return $result;
    }


    /**
     * DeleteUser action
     *
     * Deletes the specified user.
     * NOTE! The user must not belong to any groups, have any keys or signing certificates,
     * or have any attached policies.
     *
     * @param   string    $userName Name of the user to delete.
     * @return  bool      Returns TRUE if user is successfully removed.
     * @throws  IamException
     * @throws  ClientException
     */
    public function deleteUser ($userName)
    {
        $result = false;
        $options = array(
            'UserName' => (string) $userName,
        );
        $response = $this->client->call('DeleteUser', $options);
        if ($response->getError() === false) {
            //Success
            $sxml = simplexml_load_string($response->getRawContent());
            if (!isset($sxml->ResponseMetadata)) {
                throw new IamException('Unexpected response! ' . $response->getRawContent());
            }
            $user = $this->iam->user->get($options['UserName']);
            if ($user instanceof UserData) {
                $this->getEntityManager()->detach($user);
            }
            $result = true;
        }
        return $result;
    }

    /**
     * ListAccessKeys action.
     *
     * Returns information about the Access Key IDs associated with the specified user.
     * If there are none, the action returns an empty list.
     * Although each user is limited to a small number of keys, you can still paginate the results using the
     * MaxItems and Marker parameters.
     * If the UserName field is not specified, the UserName is determined implicitly based on the
     * AWS Access Key ID used to sign the request. Because this action works for access keys under the AWS account,
     * this API can be used to manage root credentials even if the AWS account has no associated users.
     * Note!
     * To ensure the security of your AWS account, the secret access key is accessible only during key
     * and user creation.
     *
     * @param   string       $userName optional
     * @param   string       $marker   optional
     * @param   int          $maxItems optional
     * @return  AccessKeyMetadataList Returns information about access keys
     * @throws  IamException
     * @throws  ClientException
     */
    public function listAccessKeys ($userName = null, $marker = null, $maxItems = null)
    {
        $result = null;
        $options = array();
        if (isset($userName)) {
            $options['UserName'] = (string) $userName;
        }
        if (isset($marker)) {
            $options['Marker'] = (string) $marker;
        }
        if (isset($maxItems)) {
            $options['MaxItems'] = (int) $maxItems;
        }
        $response = $this->client->call('ListAccessKeys', $options);
        if ($response->getError() === false) {
            //Success
            $sxml = simplexml_load_string($response->getRawContent());
            if (!isset($sxml->ListAccessKeysResult)) {
                throw new IamException('Unexpected response! ' . $response->getRawContent());
            }
            $ptr = $sxml->ListAccessKeysResult;
            $result = new AccessKeyMetadataList();
            $result->setIam($this->iam);
            $result->setIsTruncated((string)$ptr->IsTruncated === 'false' ? false : true);
            if ($result->getIsTruncated()) {
                $result->setMarker((string)$ptr->Marker);
            }
            if (!empty($ptr->AccessKeyMetadata->member)) {
                foreach ($ptr->AccessKeyMetadata->member as $v) {
                    $acm = new AccessKeyMetadataData();
                    $acm
                        ->setUserName((string)$v->UserName)
                        ->setAccessKeyId((string)$v->AccessKeyId)
                        ->setStatus((string)$v->Status)
                        ->setCreateDate(
                            isset($v->CreateDate) ?
                            new \DateTime((string)$v->CreateDate, new \DateTimeZone('UTC')) :
                            new \DateTime(null, new \DateTimeZone('UTC'))
                        )
                    ;
                    $result->append($acm);
                    unset($acm);
                }
            }
        }
        return $result;
    }

    /**
     * CreateAccessKey action.
     *
     * Creates a new AWS Secret Access Key and corresponding AWS Access Key ID for the specified user.
     * The default status for new keys is Active.
     *
     * If you do not specify a user name, IAM determines the user name implicitly based on the AWS Access
     * Key ID signing the request. Because this action works for access keys under the AWS account, you can
     * use this API to manage root credentials even if the AWS account has no associated users.
     *
     * IMPORTANT!
     * To ensure the security of your AWS account, the Secret Access Key is accessible only during
     * key and user creation.You must save the key (for example, in a text file) if you want to be able
     * to access it again. If a secret key is lost, you can delete the access keys for the associated user
     * and then create new keys.
     *
     * @param   string        $userName  optional The user name that the new key will belong to.
     * @return  AccessKeyData Returns information about access key
     * @throws  IamException
     * @throws  ClientException
     */
    public function createAccessKey ($userName = null)
    {
        $result = null;
        $options = array(
            'UserName' => (string) $userName,
        );
        $response = $this->client->call('CreateAccessKey', $options);
        if ($response->getError() === false) {
            //Success
            $sxml = simplexml_load_string($response->getRawContent());
            if (!isset($sxml->CreateAccessKeyResult)) {
                throw new IamException('Unexpected response! ' . $response->getRawContent());
            }
            $result = new AccessKeyData();
            $result->setIam($this->iam);
            if (!empty($sxml->CreateAccessKeyResult->AccessKey)) {
                $ptr = $sxml->CreateAccessKeyResult->AccessKey;
                $result
                    ->setUserName((string)$ptr->UserName)
                    ->setAccessKeyId((string)$ptr->AccessKeyId)
                    ->setStatus((string)$ptr->Status)
                    ->setSecretAccessKey((string)$ptr->SecretAccessKey)
                    ->setCreateDate(
                        isset($ptr->CreateDate) ?
                        new \DateTime((string)$ptr->CreateDate, new \DateTimeZone('UTC')) :
                        new \DateTime(null, new \DateTimeZone('UTC'))
                    )
                ;
            }
        }
        return $result;
    }

    /**
     * DeleteAccessKey action
     *
     * Deletes the access key associated with the specified user.
     *
     * If you do not specify a user name, IAM determines the user name implicitly based on the AWS Access
     * Key ID signing the request. Because this action works for access keys under the AWS account, you can
     * use this API to manage root credentials even if the AWS account has no associated users.
     *
     * @param   string     $accessKeyId The Access Key ID for the Access Key ID
     *                                  and Secret Access Key you want to delete.
     * @param   string     $userName    optional Name of the user whose key you want to delete.
     * @return  bool       Returns TRUE if access key is successfully removed.
     * @throws  IamException
     * @throws  ClientException
     */
    public function deleteAccessKey ($accessKeyId, $userName = null)
    {
        $result = false;
        $options = array(
            'AccessKeyId' => (string) $accessKeyId,
        );
        if ($userName !== null) {
            $options['UserName'] = (string) $userName;
        }
        $response = $this->client->call('DeleteAccessKey', $options);
        if ($response->getError() === false) {
            //Success
            $sxml = simplexml_load_string($response->getRawContent());
            if (!isset($sxml->ResponseMetadata)) {
                throw new IamException('Unexpected response! ' . $response->getRawContent());
            }
            $result = true;
        }
        return $result;
    }

    /**
     * Gets an entity manager
     *
     * @return EntityManager
     */
    public function getEntityManager ()
    {
        return $this->iam->getEntityManager();
    }
}