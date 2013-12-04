<?php
namespace Scalr\Service\Aws\Iam\DataType;

use Scalr\Service\Aws\IamException;
use Scalr\Service\Aws\Iam\AbstractIamDataType;

/**
 * AccessKeyData
 *
 * The AccessKey data type contains information about an AWS access key.
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     13.11.2012
 */
class AccessKeyData extends AbstractIamDataType
{
    /**
     * Name of the user the key is associated with.
     * Length constraints: Minimum length of 1. Maximum length of 64.
     * @var string
     */
    public $userName;

    /**
     * The ID for this access key.
     * Length constraints: Minimum length of 16. Maximum length of 32.
     * @var string
     */
    public $accessKeyId;

    /**
     * The secret key used to sign requests.
     * @var string
     */
    public $secretAccessKey;

    /**
     * The date when the access key was created.
     * @var \DateTime
     */
    public $createDate;

    /**
     * The status of the access key. Active means the key is valid for API calls, while Inactive means it is not.
     * Valid Values: Active | Inactive
     * @var string
     */
    public $status;

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
        if ($this->accessKeyId === null) {
            throw new IamException(sprintf(
                'accessKeyId has not been initialized for the object %s yet.', get_class($this)
            ));
        }
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
     * @return  bool       Returns TRUE if access key is successfully removed.
     * @throws  IamException
     * @throws  QueryClientException
     */
    public function delete ()
    {
        return $this->getIam()->user->deleteAccessKey($this->accessKeyId, $this->userName);
    }
}