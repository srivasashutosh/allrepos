<?php
namespace Scalr\Service\Aws\Client;

/**
 * Client interface
 *
 * Its main task to ensure abstraction layer between different
 * types of transporting client for the easiest scalability.
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     20.09.2012
 */
interface ClientInterface
{

    /**
     * Calls Amazon web service method.
     *
     * It ensures execution of the certain AWS action by transporting the request
     * and receiving response.
     *
     * @param     string    $action           An Web service API action name.
     * @param     array     $options          An options array.
     * @param     string    $path    optional A relative path.
     * @return    ClientResponseInterface
     * @throws    ClientException
     */
    public function call ($action, $options, $path = '/');

    /**
     * Gets client type
     *
     * @return  string Returns client type
     */
    public function getType();

    /**
     * Sets aws instance
     *
     * @param   \Scalr\Service\Aws $aws AWS intance
     * @return  ClientInterface
     */
    public function setAws(\Scalr\Service\Aws $aws = null);

    /**
     * Gets AWS instance
     * @return  \Scalr\Service\Aws Returns an AWS intance
     */
    public function getAws();
}
