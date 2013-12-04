<?php
namespace Scalr\Service\Aws\Rds\DataType;

use Scalr\Service\Aws\RdsException;
use Scalr\Service\Aws\Rds\AbstractRdsDataType;
use \DateTime;

/**
 * IPRangeData
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    22.03.2013
 */
class IPRangeData extends AbstractRdsDataType
{

    const STATUS_AUTHORIZING = 'authorizing';

    const STATUS_AUTHORIZED = 'authorized';

    const STATUS_REVOKING = 'revoking';

    const STATUS_REVOKED = 'revoked';

    /**
     * Specifies the IP range.
     *
     * @var string
     */
    public $cIDRIP;

    /**
     * Provides the status of the IP range.
     * Status can be "authorizing", "authorized", "revoking", and "revoked".
     *
     * @var string
     */
    public $status;

    /**
     * Constructor
     *
     * @param   string     $cidrip optional Specifies the IP range.
     * @param   string     $status optional Provides the status of the IP range.
     */
    public function __construct($cidrip = null, $status = null)
    {
        parent::__construct();
        $this->cIDRIP = $cidrip;
        $this->status = $status;
    }
}