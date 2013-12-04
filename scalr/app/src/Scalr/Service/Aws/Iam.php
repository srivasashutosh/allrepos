<?php
namespace Scalr\Service\Aws;

use Scalr\Service\Aws\Iam\Handler\UserHandler;
use Scalr\Service\Aws\Client\ClientException;
use Scalr\Service\Aws\DataType\ErrorData;
use Scalr\Service\Aws\DataType\ListDataType;
use Scalr\Service\Aws\Client\QueryClient;
use Scalr\Service\Aws;
use Scalr\Service\Aws\Iam\V20100508\IamApi;

/**
 * Amazon Simple Storage Service (S3) interface
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     13.11.2012
 * @property  \Scalr\Service\Aws\Iam\Handler\UserHandler $user An user service interface handler.
 * @method    \Scalr\Service\Aws\Iam\V20100508\IamApi getApiHandler() getApiHandler() Gets an IamApi handler.
 */
class Iam extends AbstractService implements ServiceInterface
{

    /**
     * API Version 20100508
     */
    const API_VERSION_20100508 = '20100508';

    /**
     * Current version of the API
     */
    const API_VERSION_CURRENT = self::API_VERSION_20100508;

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.ServiceInterface::getAllowedEntities()
     */
    public function getAllowedEntities()
    {

        return array('user');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.ServiceInterface::getAvailableApiVersions()
     */
    public function getAvailableApiVersions()
    {
        return array(self::API_VERSION_20100508);
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.ServiceInterface::getCurrentApiVersion()
     */
    public function getCurrentApiVersion()
    {
        return self::API_VERSION_CURRENT;
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.ServiceInterface::getUrl()
     */
    public function getUrl()
    {
        return 'iam.amazonaws.com';
    }
}

/*
 TODO [postponed] Following IAM API actions need to be implemented:
    AddRoleToInstanceProfile
    AddUserToGroup
    ChangePassword
    CreateAccountAlias
    CreateGroup
    CreateInstanceProfile
    CreateLoginProfile
    CreateRole
    CreateVirtualMFADevice
    DeactivateMFADevice
    DeleteAccountAlias
    DeleteAccountPasswordPolicy
    DeleteGroup
    DeleteGroupPolicy
    DeleteInstanceProfile
    DeleteLoginProfile
    DeleteRole
    DeleteRolePolicy
    DeleteServerCertificate
    DeleteSigningCertificate
    DeleteVirtualMFADevice
    EnableMFADevice
    GetAccountPasswordPolicy
    GetAccountSummary
    GetGroup
    GetGroupPolicy
    GetInstanceProfile
    GetLoginProfile
    GetRole
    GetRolePolicy
    GetServerCertificate
    ListAccessKeys
    ListAccountAliases
    ListGroupPolicies
    ListGroups
    ListGroupsForUser
    ListInstanceProfiles
    ListInstanceProfilesForRole
    ListMFADevices
    ListRolePolicies
    ListRoles
    ListServerCertificates
    ListSigningCertificates
    ListUserPolicies
    ListUsers
    ListVirtualMFADevices
    PutGroupPolicy
    PutRolePolicy
    RemoveRoleFromInstanceProfile
    RemoveUserFromGroup
    ResyncMFADevice
    UpdateAccessKey
    UpdateAccountPasswordPolicy
    UpdateAssumeRolePolicy
    UpdateGroup
    UpdateLoginProfile
    UpdateServerCertificate
    UpdateSigningCertificate
    UpdateUser
    UploadServerCertificate
    UploadSigningCertificate
 */