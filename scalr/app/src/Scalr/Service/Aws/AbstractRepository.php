<?php
namespace Scalr\Service\Aws;

use \ArrayObject;

/**
 * AbstractRepository
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     03.10.2012
 */
abstract class AbstractRepository
{

    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Gets an EntityManager
     *
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->em;
    }

    /**
     * Gets reflection class name.
     *
     * The name of the class that represents entity object.
     *
     * @return    string  Returns reflection class name
     */
    abstract public function getReflectionClassName();

    /**
     * Gets an identifier name(s)
     *
     * @return string|array Returns the Identifier
     */
    abstract public function getIdentifier();

    /**
     * Finds one element in entity manager by its id
     *
     * @param    string      $id  Element Id (Public property of entity)
     * @return   object|null Returns one object or NULL if nothing found.
     */
    public function find($id)
    {
        $id = (array) $id;
        $em = $this->getEntityManager();
        $storage = $em->getStorage($this->getReflectionClassName());
        return isset($storage) ? $storage->find($id) : null;
    }

    /**
     * Finds one element by required criteria.
     *
     * @param     array    $criteria An assoc array with search query. It looks like array (propertyname => value)
     * @return    object|null Returns an entity or null if nothing found.
     */
    public function findOneBy(array $criteria)
    {
        $em = $this->getEntityManager();
        $storage = $em->getStorage($this->getReflectionClassName());
        foreach ($storage as $obj) {
            $c = true;
            foreach ($criteria as $propertyName => $value) {
                $fn = 'get' . ucfirst($propertyName);
                if ($obj->$fn() !== $value) {
                    $c = false;
                    break;
                }
            }
            if ($c === true) {
                return $obj;
            }
        }
        return null;
    }

    /**
     * Finds elements by required criteria.
     *
     * @param     array        $criteria An assoc array with search query. It looks like array (propertyname => value)
     * @return    ArrayObject  Returns an list of entities which match criteria.
     */
    public function findBy(array $criteria)
    {
        $result = new ArrayObject;
        $em = $this->getEntityManager();
        $storage = $em->getStorage($this->getReflectionClassName());
        foreach ($storage as $obj) {
            $c = true;
            foreach ($criteria as $propertyName => $value) {
                $fn = 'get' . ucfirst($propertyName);
                if ($obj->$fn() !== $value) {
                    $c = false;
                    break;
                }
            }
            if ($c === true) {
                $result->append($obj);
            }
        }
        return $result;
    }
}