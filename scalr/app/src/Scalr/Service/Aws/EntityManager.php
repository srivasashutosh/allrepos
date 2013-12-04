<?php
namespace Scalr\Service\Aws;

use Scalr\Service\AwsException;

/**
 * AWS EntityManager
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     03.10.2012
 */
class EntityManager
{
    /**
     * Object Storage list
     *
     * @var array
     */
    protected $storage = array();

    protected $repos = array();

    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Gets storage by class name
     *
     * @param    string    $class   A class name
     * @return   EntityStorage  Returns storage object
     */
    public function getStorage($class)
    {
        if (isset($this->storage[$class])) {
            $storage = $this->storage[$class];
        } else {
            $storage = null;
        }
        return $storage;
    }

    /**
     * Gets repository
     *
     * @param     string     $repository   Repository Name. For an instance Elb:LoadBalancerDescription
     * @throws    \InvalidArgumentException
     * @throws    AwsException
     * @return    AbstractRepository  Returns Repository instance
     */
    public function getRepository($repository)
    {
        $arr = preg_split('/\:/', (string) $repository);
        if (count($arr) == 1) {
            $class = $arr[0];
        } else if (count($arr) == 2) {
            $class = __NAMESPACE__ . '\\Repository\\' . $arr[0] . $arr[1] . 'Repository';
        } else {
            throw new \InvalidArgumentException('Invalid repository name ' . $repository);
        }
        if (!isset($this->repos[$class])) {
            $this->repos[$class] = new $class ($this);
            if (!($this->repos[$class] instanceof AbstractRepository)) {
                throw new AwsException('Invalid repository class ' . get_class($repository) . '. ' . 'It must be instance of Scalr\\Service\\AwsAbstractRepository class.');
            }
        }
        return $this->repos[$class];
    }

    /**
     * Gets repository class that is associated with required entity.
     *
     * @param     object|string     $entity   An entity obejct or string class name
     * @return    AbstractRepository   Returns repository class which is associated with required entity.
     */
    public function getEntityRepository($entity)
    {
        $class = is_object($entity) ? get_class($entity) : (string) $entity;
        if (preg_match('/\\\\([^\\\\]+)\\\\DataType\\\\([^\\\\]+)Data$/', $class, $matches)) {
            $repository = $matches[1] . ':' . $matches[2];
        } else {
            throw new AwsException('Could not determine repository class for ' . $class);
        }
        return $this->getRepository($repository);
    }

    /**
     * Attaches an antity
     *
     * @param     object     $entity  Entity Object
     * @throws InvalidArgumentException
     */
    public function attach($entity)
    {
        if (!is_object($entity)) {
            throw new \InvalidArgumentException('Entity must be object!');
        }
        $class = get_class($entity);
        if (!isset($this->storage[$class])) {
            $this->storage[$class] = new EntityStorage($this->getEntityRepository($class));
        }
        /* @var $storage EntityStorage */
        $storage = $this->storage[$class];
        $storage->attach($entity);
    }

    /**
     * Removes an entity
     *
     * @param   object    $entity  Entity Object
     * @throws InvalidArgumentException
     */
    public function detach($entity)
    {
        if (!is_object($entity)) {
            throw new \InvalidArgumentException('Entity must be object!');
        }
        $class = get_class($entity);
        if (isset($this->storage[$class])) {
            /* @var $storage EntityStorage */
            $storage = $this->storage[$class];
            $storage->detach($entity);
        }
    }

    /**
     * Removes all entities from all repositories
     */
    public function detachAll()
    {
        foreach ($this->storage as $storage) {
            $storage->detachAll();
        }
    }
}