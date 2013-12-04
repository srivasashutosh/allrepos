<?php
namespace Scalr\Service\OpenStack\Type;

/**
 * Application Format class
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    06.12.2012
 */
class AppFormat extends StringType
{
    const APP_XML  = 'xml';
    const APP_JSON = 'json';

    protected static function getPrefix()
    {
        return 'APP_';
    }
}
