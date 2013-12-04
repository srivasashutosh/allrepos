<?php
namespace Scalr\Service\Aws;

/**
 * LoaderInterface
 *
 * Descrbies interface for loading data into objects from xml
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     01.10.2012
 */
interface LoaderInterface
{

    /**
     * Loads xml into loader
     *
     * @param   string    $xml  XML
     * @return  mixed     Returns result of loading
     */
    public function load($xml);

    /**
     * Gets result object that is loaded
     *
     * @return   mixed  Returns result of loading
     */
    public function getResult();
}