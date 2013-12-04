<?php
namespace Scalr\Service\OpenStack\Services\Network\Type;

use Scalr\Service\OpenStack\Type\AbstractInitType;

/**
 * AllocationPool
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    09.05.2013
 */
class AllocationPool extends AbstractInitType
{
    /**
     * Start ip
     *
     * @var string
     */
    public $start;

    /**
     * End IP
     *
     * @var string
     */
    public $end;

    /**
     * Constructor
     * @param   string     $start  Start IP
     * @param   string     $end    End IP
     */
    public function __construct($start = null, $end = null)
    {
        $this->start = $start;
        $this->end = $end;
    }

    /**
     * Initializes a new AllocationPool
     *
     * @param   string     $start  Start IP
     * @param   string     $end    End IP
     * @return  AllocationPool
     */
    public static function init($start = null, $end = null)
    {
        return call_user_func_array('parent::init', func_get_args());
    }

    /**
     * Gets the start IP
     *
     * @return  string  Returns the start ip
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Gets the end IP
     *
     * @return  string Returns the end IP
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Sets the start IP
     *
     * @param   string $start The Start IP
     * @return  AllocationPool
     */
    public function setStart($start)
    {
        $this->start = $start;
        return $this;
    }

    /**
     * Sets the end IP
     *
     * @param   string $end The end IP
     * @return  AllocationPool
     */
    public function setEnd($end)
    {
        $this->end = $end;
        return $this;
    }
}