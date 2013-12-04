<?php

class Scalr_Exception_LimitExceeded extends Exception
{
    function __construct($limitName)
    {
        parent::__construct(sprintf(_("%s limit exceeded for your account. Please <a href='#/billing'>upgrade your account</a> to higher plan"), $limitName));
    }
}
