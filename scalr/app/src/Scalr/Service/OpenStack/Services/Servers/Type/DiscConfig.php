<?php
namespace Scalr\Service\OpenStack\Services\Servers\Type;

use Scalr\Service\OpenStack\Type\StringType;

/**
 * DiscConfig
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    11.12.2012
 */
class DiscConfig extends StringType
{
    const VAL_AUTO   = 'AUTO';
    const VAL_MANUAL = 'MANUAL';
}