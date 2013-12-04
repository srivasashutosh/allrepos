<?php
namespace Scalr\Service\OpenStack\Type;

/**
 * AbstractInitType
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    07.12.2012
 */
abstract class AbstractInitType
{
    /**
     * Initializes a new object of the class
     *
     * @return AbstractInitType
     */
    public static function init()
    {
        $class = get_called_class();
        $obj = new $class;
        $args = func_get_args();
        if (!empty($args)) {
            call_user_func_array(array($obj, '__construct'), $args);
        }
        return $obj;
    }

    /**
     * Initializes a new object of the class with the specified array
     *
     * Array should look like array('property' => value) where for an each
     * property in the specified array, the method setProperty must exist.
     *
     * @param   array|\Traversable  $array  The properties
     * @throws  \BadFunctionCallException
     */
    public static function initArray($array)
    {
        $class = get_called_class();
        if (!is_array($array) && !($array instanceof \Traversable)) {
            throw new \BadFunctionCallException(sprintf(
                'Infalid argument for the field. Either "%s" or array is accepted.',
                $class
            ));
        }
        $obj = new $class;
        foreach ($array as $opt => $val) {
            $methodName = "set" . ucfirst($opt);
            if (!method_exists($obj, $methodName)) {
                if (!property_exists($obj, $opt)) {
                    throw new \BadFunctionCallException(sprintf(
                        'Neither method "%s" nor property "%s" does exist for the %s class.',
                        $methodName, $opt, $class
                    ));
                }
                $obj->$opt = $val;
            } else {
                $obj->$methodName($val);
            }
        }
        return $obj;
    }
}