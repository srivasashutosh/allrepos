<?php

require_once __DIR__ . '/../src/prepend.inc.php';

error_reporting(E_ALL | E_STRICT);

if (!function_exists('apache_request_headers')) {
	//Workaround for the phpunit WebTestCase class.
	function apache_request_headers()
	{
		return array();
	}
}

//Turns off error handler
restore_error_handler();
ini_set('max_execution_time', 10000);