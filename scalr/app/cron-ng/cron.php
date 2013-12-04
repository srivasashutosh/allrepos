<?php

declare(ticks = 1);

define('SCALR_MULTITHREADING', true);
define("NO_TEMPLATES", true);
define("NO_SESSIONS", true);

require_once (dirname(__FILE__) . "/../src/prepend.inc.php");

$launcher = new Scalr_System_Cronjob_Launcher(array(
    "jobDir"       => __DIR__ . "/jobs",
    "clsNamespace" => "Scalr_Cronjob"
));
$launcher->launch();