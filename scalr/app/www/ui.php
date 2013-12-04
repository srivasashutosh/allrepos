<?php

//This handler is rewritten by prepend.inc.php
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && (
        $error['type'] == E_ERROR ||
        $error['type'] == E_PARSE ||
        $error['type'] == E_COMPILE_ERROR
    )) {
        if (! headers_sent()) {
            header("HTTP/1.0 500");
        }
    }
});

$path = trim(str_replace("?{$_SERVER['QUERY_STRING']}", "", $_SERVER['REQUEST_URI']), '/');

define('SCALR_NOT_CHECK_SESSION', 1);

try {
    $startTime = microtime(true);
    require __DIR__ . '/src/prepend.inc.php';
    $prependTime = microtime(true);

    $session = Scalr_Session::getInstance();
    try {
        $request = Scalr_UI_Request::initializeInstance(Scalr_UI_Request::REQUEST_TYPE_UI, $session->getUserId(), $session->getEnvironmentId());
    } catch (Exception $e) {
        if ($path == 'guest/logout') {
            // hack
            Scalr_Session::destroy();
            Scalr_UI_Response::getInstance()->setRedirect('/');
            Scalr_UI_Response::getInstance()->sendResponse();
            exit;
        }
        $message = $e->getMessage();
        if ($e->getCode() != 1)
            $message = $message . ' <a href="/guest/logout">Click here to login as another user</a>';
        throw new Exception($message);
    }

    if ($session->isAuthenticated()) {
        $session->setEnvironmentId($request->getEnvironment()->id);
    }

    $initTime = microtime(true);

    Scalr_UI_Response::getInstance()->setHeader("X-Scalr-PrependTime", $prependTime-$startTime);
    Scalr_UI_Response::getInstance()->setHeader("X-Scalr-InitTime", $initTime-$prependTime);

    Scalr_UI_Controller::handleRequest(explode('/', $path), $_REQUEST);

} catch (ADODB_Exception $e) {
    Scalr_UI_Response::getInstance()->data(array('errorDB' => true));
    Scalr_UI_Response::getInstance()->failure();
    Scalr_UI_Response::getInstance()->sendResponse();
} catch (Exception $e) {
    Scalr_UI_Response::getInstance()->failure($e->getMessage());
    Scalr_UI_Response::getInstance()->sendResponse();
}
