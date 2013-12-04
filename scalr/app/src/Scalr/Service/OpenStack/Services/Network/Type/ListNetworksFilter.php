<?php
namespace Scalr\Service\OpenStack\Services\Network\Type;

use Scalr\Service\OpenStack\Type\BooleanType;
use Scalr\Service\OpenStack\Type\Marker;
use \DateTime;

/**
 * ListNetworksFilter
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    08.05.2013
 */
class ListNetworksFilter extends Marker
{

    /**
     * Filters the list of networks by name
     *
     * @var array
     */
    private $name;

    /**
     * Filters the list of networks by status
     *
     * @var array
     */
    private $status;

    /**
     * Filter by admin state flag
     *
     * @var BooleanType
     */
    private $adminStateUp;

    /**
     * Filter by shared
     *
     * @var BooleanType
     */
    private $shared;

    /**
     * Filter by id
     *
     * @var array
     */
    private $id;

    /**
     * Convenient constructor
     *
     * @param   string|array            $name         optional The one or more name of the network
     * @param   NetworkStatusType|array $status       optional The one or more status of the Network
     * @param   bool                    $adminStateUp optional The admin state
     * @param   bool                    $shared       optional The shared flag
     * @param   string|array            $id           optional The one or more ID of the network
     * @param   string                  $marker       optional A marker.
     * @param   int                     $limit        optional Limit.
     */
    public function __construct($name = null, $status = null, $adminStateUp = null,
                                $shared = null, $id = null, $marker = null, $limit = null)
    {
        parent::__construct($marker, $limit);
        $this->setName($name);
        $this->setStatus($status);
        $this->setId($id);
        $this->setShared($shared);
        $this->setAdminStateUp($adminStateUp);
    }

    /**
     * Initializes new object
     *
     * @param   string|array            $name         optional The one or more name of the network
     * @param   NetworkStatusType|array $status       optional The one or more status of the Network
     * @param   bool                    $adminStateUp optional The admin state
     * @param   bool                    $shared       optional The shared flag
     * @param   string|array            $id           optional The one or more ID of the network
     * @param   string                  $marker       optional A marker.
     * @param   int                     $limit        optional Limit.
     * @return  ListNetworksFilter      Returns a new ListNetworksFilter object
     */
    public static function init($name = null, $status = null, $adminStateUp = null,
                                $shared = null, $id = null, $marker = null, $limit = null)
    {
        return call_user_func_array('parent::init', func_get_args());
    }

    /**
     * Gets the list of the network name.
     *
     * @return  array  Returns array of the name of the network to filter
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets the list of the network status.
     *
     * @return  array Returns array of the statuses to filter
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Sets one or more name of the network to filter
     *
     * @param   string $name The list of the name of the network to filter
     * @return  ListNetworksFilter
     */
    public function setName($name = null)
    {
        $this->name = array();
        return $name == null ? $this : $this->addName($name);
    }

    /**
     * Adds one or more name of the network to filter
     *
     * @param   string|array $name The name of the network to filter
     */
    public function addName($name)
    {
        return $this->_addPropertyValue('name', $name);
    }

    /**
     * Sets the list of the statuses of the network to filter
     *
     * @param   NetworkStatusType|array $status The list of the statuses to filter
     * @return  ListNetworksFilter
     */
    public function setStatus($status = null)
    {
        $this->status = array();
        return $status == null ? $this : $this->addStatus($status);
    }

    /**
     * Adds one or more status of the network to filter
     *
     * @param   NetworkStatusType|array $status The list of the statuses to filter
     * @return  ListNetworksFilter
     */
    public function addStatus($status = null)
    {
        return $this->_addPropertyValue('status', $status, function($v) {
            if (!($v instanceof NetworkStatusType)) {
                $v = new NetworkStatusType((string)$v);
            }
            return $v;
        });
    }


    /**
     * Gets admin state
     *
     * @return  boolean Returns admin state
     */
    public function getAdminStateUp()
    {
        return $this->adminStateUp;
    }

    /**
     * Gets shared flag
     *
     * @return  boolean Returns the shared flag
     */
    public function getShared()
    {
        return $this->shared;
    }

    /**
     * Gets the list of the ID of the network
     *
     * @return  array Returns the list of the ID of the network
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the admin state flag
     *
     * @param   boolean $adminStateUp The admin state flag
     * @return  ListNetworksFilter
     */
    public function setAdminStateUp($adminStateUp = null)
    {
        $this->adminStateUp = $adminStateUp !== null ? BooleanType::init($adminStateUp) : null;
        return $this;
    }

    /**
     * Sets the shared flag
     *
     * @param   boolean $shared The shared flag
     * @return  ListNetworksFilter
     */
    public function setShared($shared = null)
    {
        $this->shared = $shared !== null ? BooleanType::init($shared) : null;
        return $this;
    }

    /**
     * Sets the list of ID of the network
     *
     * @param   array|string   $id  The one or more ID of the network
     * @return  ListNetworksFilter
     */
    public function setId($id = null)
    {
        $this->id = $id;
        return $id === null ? $this : $this->addId($id);
    }

    /**
     * Adds one or more ID of the network to filter
     *
     * @param   string|array $id The one or more ID of the network to filter
     * @return  ListNetworksFilter
     */
    public function addId($id)
    {
        return $this->_addPropertyValue('id', $id);
    }

    /**
     * Adds property's value
     *
     * @param   string       $name     PropertyName
     * @param   array|string $value    value
     * @param   \Closure     $typeCast optional Type casting closrure
     * @return  ListNetworksFilter
     */
    private function _addPropertyValue($name, $value, \Closure $typeCast = null)
    {
        if (!property_exists($this, $name)) {
            throw new \InvalidArgumentException(sprintf(
                'Property "%s" does not exist in "%s"',
                $name, get_class($this)
            ));
        }
        if ($this->$name === null) {
            $this->$name = array();
        }
        $property =& $this->$name;
        if (!is_array($value) && !($value instanceof \Traversable)) {
            $value = array($value);
        }
        foreach ($value as $v) {
            if ($typeCast !== null) {
                $property[] = $typeCast($v);
            } else {
                $property[] = (string)$v;
            }
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\OpenStack\Type.Marker::getQueryData()
     */
    public function getQueryData()
    {
        $options = parent::getQueryData();

        if (!empty($this->name)) {
            $options['name'] = $this->getName();
        }
        if (!empty($this->status)) {
            $options['status'] = $this->getStatus();
        }
        if (!empty($this->id)) {
            $options['id'] = $this->getId();
        }
        if ($this->adminStateUp !== null) {
            $options['admin_state_up'] = (string)$this->getAdminStateUp();
        }
        if ($this->shared !== null) {
            $options['shared'] = (string)$this->getShared();
        }

        return $options;
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\OpenStack\Type.Marker::getQueryString()
     */
    public function getQueryString()
    {
        $str = parent::getQueryString();

        foreach (array('name', 'status', 'id') as $prop) {
            if (!empty($this->$prop)) {
                foreach ($this->$prop as $v) {
                    $str .= '&' . $prop . '=' . rawurlencode($v);
                }
            }
        }

        if ($this->adminStateUp !== null) {
            $str .= '&admin_state_up=' . ((string)$this->getAdminStateUp());
        }
        if ($this->shared !== null) {
            $str .= '&shared=' . ((string)$this->getShared());
        }

        return ltrim($str, '&');
    }
}