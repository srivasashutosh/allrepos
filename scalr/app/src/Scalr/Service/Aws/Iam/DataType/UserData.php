<?php
namespace Scalr\Service\Aws\Iam\DataType;

use Scalr\Service\Aws\IamException;
use Scalr\Service\Aws\Iam\AbstractIamDataType;

/**
 * UserData
 *
 * The User data type contains information about a user.
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     13.11.2012
 */
class UserData extends AbstractIamDataType
{

    /**
     * List of external identifier names.
     *
     * @var array
     */
    protected $_externalKeys = array();

    /**
     * The name identifying the user.
     *
     * Length constraints: Minimum length of 1. Maximum length of 64.
     *
     * @var string
     */
    public $userName;

    /**
     * The stable and unique string identifying the user
     *
     * Length constraints: Minimum length of 16. Maximum length of 32.
     *
     * @var string
     */
    public $userId;

    /**
     * The Amazon Resource Name (ARN) specifying the user.
     *
     * Length constraints: Minimum length of 20. Maximum length of 2048.
     *
     * @var string
     */
    public $arn;

    /**
     * The date when the user was created.
     *
     * @var \DateTime
     */
    public $createDate;

    /**
     * Path to the user
     *
     * Length constraints: Minimum length of 1. Maximum length of 512.
     *
     * @var string
     */
    public $path;

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws\Iam.AbstractIamDataType::throwExceptionIfNotInitialized()
     */
    protected function throwExceptionIfNotInitialized()
    {
        parent::throwExceptionIfNotInitialized();
        if ($this->userName === null) {
            throw new IamException(sprintf(
                'userName has not been initialized for the object %s yet.', get_class($this)
            ));
        }
    }

    /**
     * DeleteUser action
     *
     * Deletes the specified user.
     * NOTE! The user must not belong to any groups, have any keys or signing certificates,
     * or have any attached policies.
     *
     * @return  bool      Returns TRUE if user has been successfully removed.
     * @throws  IamException
     * @throws  ClientException
     */
    public function delete ()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getIam()->user->delete($this->getUserName());
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
     * @return  AccessKeyData Returns information about access key
     * @throws  IamException
     * @throws  ClientException
     */
    public function createAccessKey ()
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getIam()->user->createAccessKey($this->userName);
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
     * @return  bool       Returns TRUE if access key is successfully removed.
     * @throws  IamException
     * @throws  ClientException
     */
    public function deleteAccessKey ($accessKeyId)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getIam()->user->deleteAccessKey($accessKeyId, $this->userName);
    }

    /**
     * PutUserPolicy action
     *
     * Adds (or updates) a policy document associated with the specified user.
     *
     * @param   string     $policyName     Name of the policy document.
     *                                     Length constraints: Minimum length of 1. Maximum length of 128.
     * @param   string     $policyDocument The policy document.
     *                                     Length constraints: Minimum length of 1. Maximum length of 131072.
     * @return  bool       Returns true if policy is added (or updated)
     * @throws  IamException
     * @throws  ClientException
     */
    public function putPolicy ($policyName, $policyDocument)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getIam()->user->putUserPolicy($this->getUserName(), $policyName, $policyDocument);
    }

    /**
     * DeleteUserPolicy action
     *
     * @param   string     $policyName     Name of the policy document.
     *                                     Length constraints: Minimum length of 1. Maximum length of 128.
     * @return  bool       Returns true if policy is successfully removed.
     * @throws  IamException
     * @throws  ClientException
     */
    public function deletePolicy ($policyName)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getIam()->user->deleteUserPolicy($this->getUserName(), $policyName);
    }

    /**
     * GetUserPolicy action
     *
     * Retrieves the specified policy document for the specified user.
     *
     * @param   string     $policyName     Name of the policy document.
     *                                     Length constraints: Minimum length of 1. Maximum length of 128.
     * @return  string     Returns policy document
     * @throws  IamException
     * @throws  ClientException
     */
    public function getPolicy ($policyName)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getIam()->user->getUserPolicy($this->getUserName(), $policyName);
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
     * @param   string       $marker   optional
     * @param   int          $maxItems optional
     * @return  AccessKeyMetadataList Returns information about access keys
     * @throws  IamException
     * @throws  ClientException
     */
    public function listAccessKeys ($marker = null, $maxItems = null)
    {
        $this->throwExceptionIfNotInitialized();
        return $this->getIam()->user->listAccessKeys($this->getUserName(), $marker, $maxItems);
    }
}