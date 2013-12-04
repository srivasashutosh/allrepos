<?php

require_once __DIR__ . "/../../src/prepend.inc.php";

define("NOW", str_replace("..","", substr(basename($_SERVER['PHP_SELF']),0, -4))); // @TODO: remove with old templates