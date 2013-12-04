<?php
    final class ENVIRONMENT_SETTINGS
    {
        const TIMEZONE				= 'timezone'; // DEPRECATED

        const CLOUDYN_ENABLED		= 'cloudyn.enabled';
        const CLOUDYN_AWS_ACCESSKEY	= 'cloudyn.aws.accesskey';
        const CLOUDYN_ACCOUNTID     = 'cloudyn.accountid';

        const API_LIMIT_ENABLED     = 'api.limit.enabled';
        const API_LIMIT_REQPERHOUR  = 'api.limit.requests_per_hour';
        const API_LIMIT_HOUR        = 'api.limit.hour';
        const API_LIMIT_USAGE       = 'api.limit.usage';
    }
