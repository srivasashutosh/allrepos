<?php
namespace Scalr\Service\OpenStack\Services;

/**
 * OpenStack ServiceHandlerInterface
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    14.12.2012
 */
interface ServiceHandlerInterface
{
    /**
     * Gets the list of allowed methods for this handler
     *
     * @return  array Returns array looks like array('alias' => 'serviceMethod');
     */
    public function getServiceMethodAliases();
}