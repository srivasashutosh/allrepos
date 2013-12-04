<?php
namespace Scalr\Service\Aws\Iam\Handler;

use Scalr\Service\Aws\Iam\DataType\AccessKeyData;
use Scalr\Service\Aws\Iam\DataType\UserData;
use Scalr\Service\Aws\DataType\ListDataType;
use Scalr\Service\Aws\IamException;
use Scalr\Service\Aws\Client\ClientException;
use Scalr\Service\Aws\Iam\AbstractIamHandler;

/**
 * UserHandler
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     13.11.2012
 */
class UserHandler extends AbstractIamHandler
{

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
    public function create($userName, $path = null)
    {
        return $this->getIam()->getApiHandler()->createUser($userName, $path);
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
    public function fetch($userName = null)
    {
        return $this->getIam()->getApiHandler()->getUser($userName);
    }

    /**
     * DeleteUser action
     *
     * Deletes the specified user.
     * NOTE! The user must not belong to any groups, have any keys or signing certificates,
     * or have any attached policies.
     *
     * @param   string    $userName Name of the user to delete.
     * @return  bool      Returns TRUE if user has been successfully removed.
     * @throws  IamException
     * @throws  ClientException
     */
    public function delete($userName)
    {
        return $this->getIam()->getApiHandler()->deleteUser($userName);
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
    public function createAccessKey($userName = null)
    {
        return $this->getIam()->getApiHandler()->createAccessKey($userName);
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
    public function deleteAccessKey($accessKeyId, $userName = null)
    {
        return $this->getIam()->getApiHandler()->deleteAccessKey($accessKeyId, $userName);
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
    public function putUserPolicy($userName, $policyName, $policyDocument)
    {
        return $this->getIam()->getApiHandler()->putUserPolicy($userName, $policyName, $policyDocument);
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
    public function deleteUserPolicy($userName, $policyName)
    {
        return $this->getIam()->getApiHandler()->deleteUserPolicy($userName, $policyName);
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
    public function getUserPolicy($userName, $policyName)
    {
        return $this->getIam()->getApiHandler()->getUserPolicy($userName, $policyName);
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
    public function listAccessKeys($userName = null, $marker = null, $maxItems = null)
    {
        return $this->getIam()->getApiHandler()->listAccessKeys($userName, $marker, $maxItems);
    }

    /**
     * Gets UserData object from the Entity Storage.
     *
     * You should be aware of the fact that the entity manager is turned off by default.
     * IMPORTANT! If you want to retrive user info from the Amazon service
     * please use fetch() method of current class.
     *
     * @param   string $userName An user name to find in storage
     * @return  UserData Returns found UserData object for the given user name or NULL if it doesn't exist.
     */
    public function get($userName)
    {
        return $this->getIam()->getEntityManager()->getRepository('Iam:User')->find($userName);
    }
}