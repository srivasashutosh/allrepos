<?
    require(dirname(__FILE__)."/../src/prepend.inc.php");

    /*
     * Date: 2008-11-25
     * Initial Query-env interface
     */
    require(dirname(__FILE__)."/../src/class.ScalrEnvironment20081125.php");

    /*
     * Date: 2008-12-16
     * Added /list-ebs-mountpoints method
     * Added /get-latest-version method
     */
    require(dirname(__FILE__)."/../src/class.ScalrEnvironment20081216.php");

    /*
     * Date: 2009-03-05
     * Improved /list-role-params method (Added mysql options)
     */
    require(dirname(__FILE__)."/../src/class.ScalrEnvironment20090305.php");

    /**
     * Date: 2010-09-23
     * @todo: description
     */
    require(dirname(__FILE__)."/../src/class.ScalrEnvironment20100923.php");


    /**
     * Date: 2012-04-17
     * @todo: description
     */
    require(dirname(__FILE__)."/../src/class.ScalrEnvironment20120417.php");

    /**
     * Date: 2012-07-01
     * @todo: description
     */
    require(dirname(__FILE__)."/../src/class.ScalrEnvironment20120701.php");

    /**
     * ***************************************************************************************
     */
    if (!$_REQUEST["version"])
        die();

    $args = "";
    foreach ($_REQUEST as $k => $v)
    {
        $args .= "{$k} = {$v}, ";
    }

    $args = trim($args, ",");

    //Logger::getLogger('query-env')->info("Received request. Args: {$args} (".http_build_query($_REQUEST).")");

    try
    {
            $EnvironmentObject = ScalrEnvironmentFactory::CreateEnvironment($_REQUEST['version']);
        $response = $EnvironmentObject->Query($_REQUEST['operation'], array_merge($_GET, $_POST));
    }
    catch(Exception $e)
    {
        header("HTTP/1.0 500 Error");

        if (!stristr($e->getMessage(), "not found in database") && !stristr($e->getMessage(), "ami-scripts"))
           $Logger->error(sprintf(_("Exception thrown in query-env interface: %s"), $e->getMessage()));

        die($e->getMessage());
    }

    header("Content-Type: text/xml");

    //Logger::getLogger('query-env')->info("Response:");
    //Logger::getLogger('query-env')->info($response);
    print $response;
    exit();
?>