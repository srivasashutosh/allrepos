<?php

namespace Scalr\Logger\AuditLog\Documents;

use \ArrayObject;
use \MongoDate;

/**
 * Abstract audit log document
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    31.10.2012
 */
class AbstractAuditLogDocument extends ArrayObject
{

    /**
     * @var string
     */
    public $datatype;

    /**
     * Constructor
     * @param   mixed    $input
     */
    public function __construct($input = null)
    {
        parent::__construct((isset($input) ? $input : array()), ArrayObject::ARRAY_AS_PROPS);
        $class = get_class($this);
        if (($pos = strrpos($class, '\\')) !== false) {
            //It's required to nested objects support
            $this->datatype = substr($class, $pos + 1, -8);
        } else {
            $this->datatype = '';
        }
    }

    /**
     * Gets properties for the current document.
     *
     * It can be reflection propreties as well as magic proprties
     *
     * @return   array Returns array of the available propreties
     */
    public static function getDocumentProperties()
    {
        static $ret = array();
        $class = get_called_class();
        if (!isset($ret[$class])) {
            $reflectionClass = new \ReflectionClass($class);
            foreach ($reflectionClass->getProperties() as $prop) {
                $ret[$class][$prop->getName()] = null;
            }
        }
        return $ret[$class];
    }

    /**
     * Gets an datatype
     *
     * @return string Returns data type
     */
    public function getDatatype()
    {
        return $this->datatype;
    }

    /**
     * This allows to load Document object from source object using property mapping array.
     *
     * @param   object     $srcObject   Source object to load from.
     * @param   array      $mapping     optional Properties mapping array.
     *                                  It looks like array(documentProperty => srcPropertyOrMethodName).
     *                                  If all properties in the document object have the same names as
     *                                  in the soruce object it can be avoided.
     * @return  AbstractAuditLogDocument Returns new Document in which all properties are loaded from source object.
     */
    protected static function loadFrom($srcObject, array $mapping = null)
    {
        $class = get_called_class();
        $result = new $class;
        $srcRef = new \ReflectionClass(get_class($srcObject));
        foreach ($mapping as $prop => $srcProperty) {
            if (property_exists($srcObject, $srcProperty) &&
                ($refsrcprop = $srcRef->getProperty($srcProperty)) &&
                $refsrcprop->isPublic()) {
                $value = $refsrcprop->getValue($srcObject);
                unset($refsrcprop);
            } else if (method_exists($srcObject, $srcProperty) &&
                      ($reflectionMethod = $srcRef->getMethod($srcProperty)) &&
                       $reflectionMethod->isPublic()) {
                $value = $reflectionMethod->invoke($srcObject);
                unset($reflectionMethod);
            } else if (method_exists($srcObject, 'get' . ucfirst($srcProperty)) &&
                      ($reflectionMethod = $srcRef->getMethod('get' . ucfirst($srcProperty))) &&
                       $reflectionMethod->isPublic()) {
                $value = $reflectionMethod->invoke($srcObject);
                unset($reflectionMethod);
            } else {
                //It supposes magic properties
                if (method_exists($srcObject, '__get')) {
                    try {
                        $mgetter = true;
                        $value = $srcObject->$srcProperty;
                    } catch (\Exception $e) {
                        $exeptionThrown = true;
                    }
                }
                //By the end we check magic getter
                if (isset($exeptionThrown) || !isset($mgetter)) {
                    $exeptionThrown = null;
                    $mgetter = null;
                    if (method_exists($srcObject, '__call')) {
                        try {
                            $value = $srcObject->$srcProperty();
                        } catch (\Exception $e) {
                            $value = null;
                        }
                    } else $value = null;
                }
            }
            $result[$prop] = $value;
        }
        return $result;
    }
}