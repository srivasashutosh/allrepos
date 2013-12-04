<?php
namespace Scalr\Service\Aws;

/**
 * AWS EntityStorage
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     04.10.2012
 */
class EntityStorage implements \Countable, \Iterator
{

    /**
     * @var array
     */
    private $storage;

    /**
     * @var int
     */
    private $position = 0;

    /**
     * @var AbstractRepository
     */
    private $repos;

    /**
     * @var array
     */
    private $indexes;

    /**
     * {@inheritdoc}
     * @see Iterator::current()
     */
    public function current()
    {
        $a = array_values($this->storage);
        return $a[$this->position];
    }

    /**
     * {@inheritdoc}
     * @see Iterator::key()
     */
    public function key()
    {
        $a = array_keys($this->storage);
        return $a[$this->position];
    }

    /**
     * {@inheritdoc}
     * @see Iterator::next()
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * {@inheritdoc}
     * @see Iterator::rewind()
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * {@inheritdoc}
     * @see Iterator::valid()
     */
    public function valid()
    {
        $a = array_values($this->storage);
        return isset($a[$this->position]);
    }

    /**
     * {@inheritdoc}
     * @see Countable::count()
     */
    public function count()
    {
        return $this->storage->count();
    }

    /**
     * Constructor
     *
     * @param   AbstractRepository   $repos  An repository which manages an this type of entities
     */
    public function __construct(AbstractRepository $repos = null)
    {
        $this->repos = $repos;
        $this->storage = array();
        $this->indexes = array();
    }

    /**
     * Gets identifier value for required object
     *
     * @param    object    $object  An entity
     * @return   string|null        Returns identifier value or null if repository isn't provided.
     */
    public function getIdentifierValue($object)
    {
        if ($this->repos !== null) {
            $value = '';
            foreach ((array)$this->repos->getIdentifier() as $property) {
                if (property_exists($object, $property)) {
                    $value .= "," . ((string) $object->$property);
                } else {
                    $fn = 'get' . ucfirst($property);
                    $value .= "," . ((string) $object->$fn());
                }
            }
            $value = $value !== '' ? substr($value, 1) : '';
        } else {
            $value = null;
        }
        return $value;
    }

    /**
     * Finds object in storage by its Identifier value
     *
     * @param    string|array    $id   Identifier value
     * @return   object|null     Returns entity object or null if nothing found.
     */
    public function find($id)
    {
        $value = '';
        foreach ((array) $id as $v) {
            $value .= ',' . (string) $v;
        }
        if ($value != '') {
            $value = substr($value, 1);
        }
        if ($this->repos !== null) {
            if (array_key_exists($value, $this->indexes) && isset($this->storage[$this->indexes[$value]])) {
                return $this->storage[$this->indexes[$value]];
            }
        }
        return null;
    }

    /**
     * Attaches an entity
     *
     * @param    object    $object  An entity
     */
    public function attach($object)
    {
        $hash = spl_object_hash($object);
        if ($this->repos !== null) {
            $id = $this->getIdentifierValue($object);
            if (array_key_exists($id, $this->indexes)) {
                unset($this->storage[$this->indexes[$id]]);
            }
            $this->indexes[$id] = $hash;
        }
        $this->storage[$hash] = $object;
    }

    /**
     * Detaches an entity
     *
     * @param    object   $object  An entity
     */
    public function detach($object)
    {
        $hash = spl_object_hash($object);
        if ($this->repos !== null) {
            $id = $this->getIdentifierValue($object);
            unset($this->indexes[$id]);
        }
        if (array_key_exists($hash, $this->storage)) {
            unset($this->storage[$hash]);
        }
    }

    /**
     * Detaches all entities
     */
    public function detachAll()
    {
        foreach ($this->storage as $k => $v) {
            unset($this->storage[$k]);
        }
    }

    /**
     * Gets storage
     *
     * @return  array  Returns storage array
     */
    public function getStorage()
    {
        return $this->storage;
    }
}