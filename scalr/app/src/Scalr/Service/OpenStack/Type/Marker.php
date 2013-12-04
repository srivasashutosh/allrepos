<?php
namespace Scalr\Service\OpenStack\Type;

/**
 * Marker
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    07.12.2012
 */
class Marker extends AbstractInitType
{
    /**
     * Maximum number of items at time (<=1000)
     * @var int
     */
    private $limit;

    /**
     * The ID of the last item in the previous list.
     * @var string
     */
    private $marker;


    /**
     * Convenient constuctor
     *
     * @param   strinng    $marker  optional The ID of the last item in the previous list.
     * @param   int        $limit   optional Maximum number of items at time (<=1000)
     */
    public function __construct($marker = null, $limit = null)
    {
        $this
            ->setLimit($limit)
            ->setMarker($marker)
        ;
    }

    /**
     * getLimit
     *
     * @return  number Maximum number of items at time (<=1000)
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * getMarker
     *
     * @return  string The ID of the last item in the previous list.
     */
    public function getMarker()
    {
        return $this->marker;
    }

    /**
     * setLimit
     *
     * @param   number $limit Maximum number of items at time (<=1000)
     * @return  Marker
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * setMarker
     *
     * @param   string $marker The ID of the last item in the previous list.
     * @return  Marker
     */
    public function setMarker($marker)
    {
        $this->marker = $marker;
        return $this;
    }

    /**
     * Initializes new object
     *
     * @param   strinng    $marker  optional The ID of the last item in the previous list.
     * @param   int        $limit   optional Maximum number of items at time (<=1000)
     * @return  Marker Returns new Marker
     */
    public static function init($marker = null, $limit = null)
    {
        return call_user_func_array('parent::init', func_get_args());
    }

    /**
     * Gets query data array
     *
     * @return array Returns query data array
     */
    public function getQueryData()
    {
        $options = array();

        if ($this->getMarker() !== null) {
            $options['marker'] = (string) $this->getMarker();
        }
        if ($this->getLimit() !== null) {
            $options['limit'] = (int) $this->getLimit();
        }

        return $options;
    }

    /**
     * Gets a query string
     *
     * @return string Returns a query string
     */
    public function getQueryString()
    {
        $str = '';
        if ($this->getMarker() !== null) {
            $str .= '&marker=' . rawurlencode($this->getMarker());
        }
        if ($this->getLimit() !== null) {
            $str .= '&limit=' . intval($this->getLimit());
        }
        return ltrim($str, '&');
    }
}