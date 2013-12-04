<?php
namespace Scalr\Service\OpenStack\Services\Network\Type;

use Scalr\Service\OpenStack\Type\BooleanType;
use Scalr\Service\OpenStack\Type\Marker;
use \DateTime;

/**
 * ListPortsFilter
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    08.05.2013
 */
class ListPortsFilter extends Marker
{

    /**
     * Filters the list of ports by name
     *
     * @var array
     */
    private $name;

    /**
     * Filter by id
     *
     * @var array
     */
    private $id;

    //TODO Additional filters can be added

    /**
     * Convenient constructor
     *
     * @param   string|array        $name         optional The one or more name of the subnet
     * @param   string|array        $id           optional The one or more ID of the subnet
     * @param   string              $marker       optional A marker.
     * @param   int                 $limit        optional Limit.
     */
    public function __construct($name = null, $id = null, $marker = null, $limit = null)
    {
        parent::__construct($marker, $limit);
        $this->setName($name);
        $this->setId($id);
    }

    /**
     * Initializes new object
     *
     * @param   string|array        $name         optional The one or more name of the subnet
     * @param   string|array        $id           optional The one or more ID of the subnet
     * @param   string              $marker       optional A marker.
     * @param   int                 $limit        optional Limit.
     * @return  ListSubnetsFilter  Returns a new ListSubnetsFilter object
     */
    public static function init($name = null, $id = null, $marker = null, $limit = null)
    {
        return call_user_func_array('parent::init', func_get_args());
    }

    /**
     * Gets the list of the subnet name.
     *
     * @return  array  Returns array of the name of the subnet to filter
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets one or more name of the subnet to filter
     *
     * @param   string $name The list of the name of the subnet to filter
     * @return  ListSubnetsFilter
     */
    public function setName($name = null)
    {
        $this->name = array();
        return $name == null ? $this : $this->addName($name);
    }

    /**
     * Adds one or more name of the subnet to filter
     *
     * @param   string|array $name The name of the subnet to filter
     */
    public function addName($name)
    {
        return $this->_addPropertyValue('name', $name);
    }

    /**
     * Gets the list of the ID of the subnet
     *
     * @return  array Returns the list of the ID of the subnet
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the list of ID of the subnet
     *
     * @param   array|string   $id  The one or more ID of the subnet
     * @return  ListSubnetsFilter
     */
    public function setId($id = null)
    {
        $this->id = $id;
        return $id === null ? $this : $this->addId($id);
    }

    /**
     * Adds one or more ID of the subnet to filter
     *
     * @param   string|array $id The one or more ID of the subnet to filter
     * @return  ListSubnetsFilter
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
        if (!empty($this->id)) {
            $options['id'] = $this->getId();
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

        foreach (array('name', 'id') as $prop) {
            if (!empty($this->$prop)) {
                foreach ($this->$prop as $v) {
                    $str .= '&' . $prop . '=' . rawurlencode($v);
                }
            }
        }

        return ltrim($str, '&');
    }
}