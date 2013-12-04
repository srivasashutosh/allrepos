<?php

namespace Scalr\Util;

/**
 * ClosureInvoker
 *
 * @author  Vitaliy Demidov  <vitaliy@scalr.com>
 * @since   11.06.2013
 */
class ClosureInvoker
{
    /**
     * @var \Closure
     */
    private $closure;

    /**
     * @var object
     */
    private $object;

    /**
     * Parent ClosureInvoker
     *
     * @var ClosureInvoker
     */
    public $parent;

    /**
     * Constructor
     *
     * @param   \Closure $closure The closure which needs to be invoked
     * @param   object   $object  optional The object
     */
    public function __construct(\Closure $closure, $object = null)
    {
        $this->closure = $closure;
        $this->object = $object;
    }

    /**
     * Gets object
     *
     * @return object Returns object
     */
    public function getObject()
    {
        return $this->object;
    }

    public function __call($name, $args)
    {
        if (is_object($this->object)) {
            array_unshift($args, $name, $this);
            return call_user_func_array($this->closure, $args);
        } else {
            throw new \Exception('Object has not been provided for the ClosureInvoker constructor.');
        }
    }

    /**
     * Gets parent Invoker or original object if it does not exist.
     *
     * @return ClosureInvoker|object Returns parent Invoker or object if it does not exist.
     */
    public function end()
    {
        return $this->parent instanceof ClosureInvoker ? $this->parent : $this->object;
    }
}