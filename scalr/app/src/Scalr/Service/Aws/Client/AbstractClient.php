<?php
namespace Scalr\Service\Aws\Client;

/**
 * AbstractClient
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     19.03.2013
 */
abstract class AbstractClient
{

    /**
     * Aws instance
     *
     * @var \Scalr\Service\Aws
     */
    private $aws;

    /**
     * Sets aws instance
     *
     * @param   \Scalr\Service\Aws $aws AWS intance
     * @return  ClientInterface
     */
    public function setAws(\Scalr\Service\Aws $aws = null)
    {
        $this->aws = $aws;
        return $this;
    }

    /**
     * Gets AWS instance
     * @return  \Scalr\Service\Aws Returns an AWS intance
     */
    public function getAws()
    {
        return $this->aws;
    }
}