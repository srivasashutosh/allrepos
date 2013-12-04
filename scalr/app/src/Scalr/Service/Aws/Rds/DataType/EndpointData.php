<?php
namespace Scalr\Service\Aws\Rds\DataType;

use Scalr\Service\Aws\RdsException;
use Scalr\Service\Aws\Rds\AbstractRdsDataType;
use \DateTime;

/**
 * EndpointData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    19.03.2013
 */
class EndpointData extends AbstractRdsDataType
{

    /**
     * Specifies the DNS address of the DB Instance.
     *
     * @var string
     */
    public $address;

    /**
     * Specifies the port that the database engine is listening on.
     *
     * @var int
     */
    public $port;

    /**
     * Constructor
     *
     * @param   string     $address optional Specifies the DNS address of the DB Instance.
     * @param   int        $port    optional Specifies the port that the database engine is listening on.
     */
    public function __construct($address = null, $port = null)
    {
        parent::__construct();
        $this->address = $address;
        $this->port = $port;
    }
}