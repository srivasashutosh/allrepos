<?php
namespace Scalr\Service\OpenStack\Services\Servers\Type;

/**
 * Network
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    11.12.2012
 */
class Network
{
    /**
     * @var string
     */
    public $uuid;

    /**
     * Convenient constructor
     *
     * @param   string     $uuid      Network UUID
     */
    public function __construct($uuid)
    {
        $this->uuid = $uuid;
    }
}