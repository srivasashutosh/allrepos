<?php

class Scalr_Exception_InsufficientPermissions extends Exception
{
    function __construct()
    {
        parent::__construct("Insufficient Permissions. Contact account owner to grant access.");
    }
}