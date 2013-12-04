<?php
namespace Scalr\Tests\Fixtures\Service\OpenStack\Services;

use Scalr\Service\OpenStack\Services\ServiceInterface;
use Scalr\Service\OpenStack\Services\AbstractService;

/**
 * Fixture FakeService
 */
class FakeService extends AbstractService implements ServiceInterface
{

    public static function getName()
    {
        return 'redefined';
    }

    public static function getType()
    {
        return 'compute';
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\OpenStack\Services.ServiceInterface::getVersion()
     */
    public function getVersion()
    {
        return 'V2';
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\OpenStack\Services.ServiceInterface::getEndpointUrl()
     */
    public function getEndpointUrl()
    {
        return '';
    }


}