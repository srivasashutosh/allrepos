<?php
namespace Scalr\Service\Aws;

use Scalr\Service\Aws\Client\ClientException;
use Scalr\Service\Aws\DataType\ErrorData;
use Scalr\Service\Aws\DataType\ListDataType;

/**
 * Amazon RDS interface
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     07.03.2013
 *
 * @property  \Scalr\Service\Aws\Rds\Handler\DbInstanceHandler $dbInstance
 *            DBInstance service interface handler
 *
 * @property  \Scalr\Service\Aws\Rds\Handler\DbSecurityGroupHandler $dbSecurityGroup
 *            DBSecurityGroup service interface handler
 *
 * @property  \Scalr\Service\Aws\Rds\Handler\DbParameterGroupHandler $dbParameterGroup
 *            DBParameterGroup service interface handler
 *
 * @property  \Scalr\Service\Aws\Rds\Handler\DbSnapshotHandler $dbSnapshot
 *            DBSnapshot service interface handler
 *
 * @property  \Scalr\Service\Aws\Rds\Handler\EventHandler $event
 *            Event service interface handler
 *
 * @method    \Scalr\Service\Aws\Rds\V20130110\RdsApi getApiHandler() getApiHandler()  Gets an RdsApi handler
 */
class Rds extends AbstractService implements ServiceInterface
{

    /**
     * API Version 20130110
     */
    const API_VERSION_20130110 = '20130110';

    /**
     * Current version of the API
     */
    const API_VERSION_CURRENT = self::API_VERSION_20130110;

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.ServiceInterface::getAllowedEntities()
     */
    public function getAllowedEntities()
    {
        return array('dbInstance', 'dbSecurityGroup', 'dbParameterGroup', 'dbSnapshot', 'event');
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.ServiceInterface::getAvailableApiVersions()
     */
    public function getAvailableApiVersions()
    {
        return array(self::API_VERSION_20130110);
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
        $region = $this->getAws()->getRegion();
        return 'rds.' . $region . '.amazonaws.com';
    }
}
