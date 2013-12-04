<?php
namespace Scalr\Service\Aws;

use Scalr\Service\Aws\DataType\StringType;
use Scalr\Service\Aws\Elb\AbstractElbListDataType;
use Scalr\Service\Aws\Elb\AbstractElbDataType;
use Scalr\Service\Aws\DataType\ListDataType;

/**
 * AbstractDataType
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     10.10.2012
 */
abstract class AbstractDataType extends AbstractServiceRelatedType
{

    /**
     * List of external identifier names.
     *
     * @var array
     */
    protected $_externalKeys = array();

    /**
     * List of the public properties
     * which is managed by magic getter and setters internally.
     *
     * @var  array
     */
    protected $_properties = array();

    /**
     * Reflection class of this object
     *
     * @var \ReflectionClass
     */
    private $reflectionClass;

    /**
     * Data for the properties that is managed internally.
     *
     * @var array
     */
    private $propertiesData = array();

    /**
     * List of identifier values
     *
     * @var array
     */
    private $externalIdentifierValues = array();

    /**
     * Known hashes
     *
     * @var array
     */
    private $known = array();

    /**
     * Original xml that is received from service
     *
     * @var string
     */
    private $originalXml;

    /**
     * Has this object set its services data sets.
     *
     * @var bool
     */
    private $serviceRelatedDatasetUpdated = false;

    /**
     * Has this object set its external identifiers values
     *
     * @var bool
     */
    private $externalKeysUpdated = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        if (empty($this->_externalKeys)) {
            $this->_setExternalKeysUpdated(true);
        }
    }

    /**
     * Sets original xml that is received in response from service
     *
     * @param   string     $xml  XML string
     * @return  AbstractDataType
     */
    public function setOriginalXml($xml)
    {
        $this->originalXml = $xml;
        return $this;
    }

    /**
     * Gets an original XML that is received in response from service.
     *
     * @return string Returns XML
     */
    public function getOriginalXml()
    {
        return $this->originalXml;
    }

    /**
     * Resets object including internal properties values
     * keys for which are defined in protected $_properties array.
     */
    public function resetObject()
    {
        $props = $this->getReflectionClass()->getProperties(\ReflectionProperty::IS_PUBLIC);
        /* @var $prop \ReflectionProperty */
        foreach ($props as $prop) {
            $prop->setValue($this, null);
        }
        //Resets an internal properties as well
        foreach ($this->_properties as $prop) {
            if (isset($this->propertiesData[$prop])) {
                unset($this->propertiesData[$prop]);
            }
        }
    }

    /**
     * @param  string  $name  property name
     * @return mixed
     */
    public function __get($name)
    {
        if (in_array($name, $this->_properties)) {
            return array_key_exists($name, $this->propertiesData) ? $this->propertiesData[$name] : null;
        }
        return parent::__get($name);
    }

    /**
     * @param  string  $name
     * @return boolean
     */
    public function __isset($name)
    {
        if (in_array($name, $this->_properties)) {
            return isset($this->propertiesData[$name]);
        }
        throw new \InvalidArgumentException(
            sprintf('Unknown property "%s" for the object %s', $name, get_class($this))
        );
    }

    /**
     *
     * @param unknown_type $name
     */
    public function __unset($name)
    {
        if (in_array($name, $this->_properties) && isset($this->propertiesData[$name])) {
            unset($this->propertiesData[$name]);
        } else {
            throw new \InvalidArgumentException(
                sprintf('Unknown property "%s" for the object %s', $name, get_class($this))
            );
        }
    }

    /**
     * @param   string     $name
     * @param   mixed      $data
     */
    public function __set($name, $data)
    {
        if (in_array($name, $this->_properties)) {
            $setfn = 'set' . ucfirst($name);
            if (method_exists($this, $setfn)) {
                //makes it possible to cast argument value type for an explicitly defined setter methods
                $this->$setfn($data);
            } else {
                $this->propertiesData[$name] = $data;
            }
        } else {
            throw new \InvalidArgumentException(
                sprintf('Unknown property "%s" for the object %s', $name, get_class($this))
            );
        }
    }

    /**
     * Sets external identifiers recursively
     *
     * @param  mixed  $inner
     */
    protected function setExternalIdentifiersRecursively(&$inner)
    {
        $this->known = array();
        if (($inner instanceof AbstractDataType) && !$inner->_hasInheritedDataSet()) {
            $this->_setExternalIdentifiersRecursively($this, $inner);
        }
    }

    /**
     * Sets external identifiers recursively
     *
     * @param   object  $holder
     * @param   object  $inner
     */
    private function _setExternalIdentifiersRecursively(&$holder, &$inner)
    {
        if ($inner !== null) {
            if ($inner instanceof ListDataType) {
                $dataSet = array_merge(array($inner), $inner->getOriginal());
            } else {
                $dataSet = array($inner);
            }
            //Property inheritance pattern
            foreach ($dataSet as $object) {
                if (is_object($object) && method_exists($object, 'getExternalIdentifiers')) {
                    $hash = spl_object_hash($object);
                    //Prevents from endless loops.
                    if (isset($this->known[$hash])) return;
                    $this->known[$hash] = true;

                    //Distributes Service interface instances
                    if (($object instanceof AbstractServiceRelatedType) && !$object->_getServiceRelatedDatasetUpdated()) {
                        $ins = $object->getServiceNames();
                        if (!empty($ins)) {
                            $ths = $this->getServiceNames();
                            if ($holder instanceof AbstractServiceRelatedType) {
                                $hs = $holder->getServiceNames();
                            } else $hs = array();
                            if (!empty($ths) || !empty($hs)) {
                                $cnt = 0;
                                foreach ($ins as $sn) {
                                    $fgetsn = 'get' . ucfirst($sn);
                                    $fsetsn = 'set' . ucfirst($sn);
                                    if (in_array($sn, $ths) && $this->$fgetsn() !== null) {
                                        $object->$fsetsn($this->$fgetsn());
                                        $cnt++;
                                    } else if (in_array($sn, $hs) && $holder->$fgetsn() !== null) {
                                        $object->$fsetsn($holder->$fgetsn());
                                        $cnt++;
                                    }
                                }
                                if ($cnt == count($ins)) {
                                    $object->_setServiceRelatedDatasetUpdated(true);
                                }
                            }
                            unset($ths);
                            unset($hs);
                        }
                        unset($ins);
                    }

                    if (!$object->_getExternalKeysUpdated()) {
                        $externalIds = $object->getExternalIdentifiers();
                        $cnt = 0;
                        foreach ($externalIds as $key) {
                            $property = ucfirst($key);
                            $setProperty = 'set' . $property;
                            $getProperty = 'get' . $property;
                            if (property_exists($holder, $key) && $holder->$key !== null) {
                                $object->$setProperty($holder->$key);
                                $cnt++;
                            } else if ((in_array($key, $holder->getPropertiesForInheritance()) ||
                                        in_array($key, $holder->getExternalIdentifiers())) && $holder->$getProperty() !== null) {
                                $object->$setProperty($holder->$getProperty());
                                $cnt++;
                            }
                        }
                        if ($cnt == count($externalIds)) {
                            $object->_setExternalKeysUpdated(true);
                        }
                    }

                    if ($object instanceof AbstractDataType) {
                        $props = $object->getPropertiesForInheritance();
                        foreach ($props as $p) {
                            $getProperty = 'get' . $p;
                            $sub = $object->$getProperty();
                            if (($sub instanceof AbstractDataType)) {
                                if (!$sub->_hasInheritedDataSet()) {
                                    $this->_setExternalIdentifiersRecursively($object, $sub);
                                }
                            }
                            unset($sub);
                        }
                    }
                }
            }
        }
    }

    /**
     * It allows to get|set an external identifier value or internal property value
     *
     * @param  string   $name
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        $m = array($name, substr($name, 0, 3), substr($name, 3));
        if (($m[1] == 'get' || $m[1] == 'set') && !empty($m[2])) {
            $identifier = lcfirst($m[2]);
            if ($m[1] == 'set' && count($arguments) !== 1) {
                if (count($arguments) !== 1) {
                    throw new \InvalidArgumentException(sprintf(
                        'One argument is expected for %s method of %s class.', $name, get_class($this)
                    ));
                }
            }
            if (in_array($identifier, $this->getServiceNames())) {
                //Ensures availability the service interfaces by getters and setters
                if ($m[1] == 'get') {
                    return isset($this->_services[$identifier]) ? $this->_services[$identifier] : null;
                } else {
                    //Set is expected to be here
                    $this->_services[$identifier] = $arguments[0];
                    return $this;
                }
            } else if (in_array($identifier, $this->_externalKeys)) {
                if ($m[1] == 'get') {
                    return array_key_exists($identifier, $this->externalIdentifierValues) ? $this->externalIdentifierValues[$identifier] : null;
                } else {
                    //Set is expected to be here.
                    $this->externalIdentifierValues[$identifier] = $arguments[0];
                    return $this;
                }
            } else if (in_array($identifier, $this->_properties)) {
                if ($m[1] == 'get') {
                    return array_key_exists($identifier, $this->propertiesData) ? $this->propertiesData[$identifier] : null;
                } else {
                    //Set property is expected to be here.
                    $this->propertiesData[$identifier] = $arguments[0];
                    return $this;
                }
            } else {
                if ($this->getReflectionClass()->hasProperty($identifier)) {
                    $prop = $this->getReflectionClass()->getProperty($identifier);
                    if ($prop instanceof \ReflectionProperty && $prop->isPublic()) {
                        if ($m[1] == 'get') {
                            return $prop->getValue($this);
                        } else {
                            //Set property is expected to be here.
                            $prop->setValue($this, $arguments[0]);
                            return $this;
                        }
                    }
                }
            }
        }
        throw new \BadFunctionCallException(sprintf(
            'Method "%s" does not exist for the class "%s".', $name, get_class($this)
        ));
    }

    /**
     * Gets a reflection class of this object
     *
     * @return \ReflectionClass
     */
    public function getReflectionClass()
    {
        if (!isset($this->reflectionClass)) {
            $this->reflectionClass = new \ReflectionClass($this);
        }
        return $this->reflectionClass;
    }

    /**
     * Gets an external identifier keys that are associated with this object.
     *
     * @return   array  Returns the list of the external identifiers.
     */
    public function getExternalIdentifiers()
    {
        return $this->_externalKeys;
    }

    /**
     * Get the properties which are used for inheritance properties purposes.
     *
     * @return array Returns a list of the public properties
     *               which is managed by magic getter and setters internally.
     */
    public function getPropertiesForInheritance()
    {
        return $this->_properties;
    }

    /**
     * Gets data as array.
     *
     * @param   bool      $ucase   optional If True if will uppercase key names of the array.
     * @param   array     $known   optional It's only for internal usage
     * @return  array Returns data as array
     */
    public function toArray($ucase = false, &$known = null)
    {
        $arr = array();
        if (is_null($known)) {
            $known = array();
        }
        $id = spl_object_hash($this);
        if (array_key_exists($id, $known)) return '**recursion**';
        $known[$id] = true;
        $trait = function (&$val) use($ucase, &$known)
        {
            if (is_object($val)) {
                if ($val instanceof AbstractDataType) {
                    $val = $val->toArray($ucase, $known);
                } else {
                    $val = (array) $val;
                }
            }
        };
        if ($this instanceof ListDataType) {
            foreach ($this->getOriginal() as $val) {
                $trait($val);
                $arr[] = $val;
            }
        } else {
            $props = $this->getReflectionClass()->getProperties(\ReflectionProperty::IS_PUBLIC);
            /* @var $prop \ReflectionProperty */
            foreach ($props as $prop) {
                $val = $prop->getValue($this);
                $trait($val);
                $arr[($ucase ? ucfirst($prop->getName()) : $prop->getName())] = $val;
            }
            //Passes through an internal properties as well
            foreach ($this->_properties as $prop) {
                if (isset($this->propertiesData[$prop])) {
                    $val = $this->propertiesData[$prop];
                    $trait($val);
                } else
                    $val = null;
                $arr[($ucase ? ucfirst($prop) : $prop)] = $val;
            }
        }
        return $arr;
    }

    /**
     * Gets data as XML document
     *
     * @return  string Returns object as XML document
     */

    /**
     * Gets object as XML
     *
     * @param   bool       $returnAsDom  optional Should it return DOMDocument object or plain xml string.
     *                                            If it's true it will return DOMDocument.
     * @param   array      $known        optional It's for internal usage
     * @return  \DOMDocument|string      Returns object converted into either XML string or DOMDocument, depends on
     *                                   returnAsDom option.
     */
    public function toXml($returnAsDom = false, &$known = null)
    {
        //This method is supposed to be overridden where it's needed.
        $dom = new \DOMDocument('1.0', 'UTF-8');
        throw $returnAsDom ? $dom : $dom->saveXML();
    }

    /**
     * Appends DomDocument content to given element
     *
     * @param   \DOMNode $toElement
     * @return  \DOMNode
     */
    public function appendContentToElement(\DOMNode $toElement)
    {
        $dom = $this->toXml(true);
        $child = $dom->firstChild;
        while ($child !== null) {
            $toElement->appendChild($toElement->ownerDocument->importNode($child, true));
            $child = $child->nextSibling;
        }
        return $toElement;
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Service\Aws.AbstractServiceRelatedType::getServiceNames()
     */
    public function getServiceNames()
    {
        //This method is supposed to be overridden.
        return array();
    }

    /**
     * Gets query parameters array.
     *
     * @param    string    $uriParameterName  optional Parameter name
     * @param    bool      $member            optional Should it add member prefix
     * @return   string    Returns query parameters array looks like array ( '[parameterName.member.][propName[.member.m]]' => value )
     *                     Values are not url encoded.
     */
    public function getQueryArray($uriParameterName = null, $member = true)
    {
        if (func_num_args() > 2) {
            $known = func_get_arg(2);
        }
        if (!isset($known)) $known = array();
        $id = spl_object_hash($this);
        if (array_key_exists($id, $known)) return '**recursion**';
        $known[$id] = true;

        $prefix = $uriParameterName !== null ? $uriParameterName . '.' : '';

        $fnCast = function($value) {
            return $value instanceof StringType ? (string) $value : $value;
        };
        $n = 1;
        $arr = array();
        $trait = function ($key, $val) use(&$known, $prefix, $member)
        {
            if (is_object($val) || is_array($val)) {
                if ($val instanceof AbstractDataType) {
                    $val = $val->getQueryArray(ucfirst($key), $member, $known);
                } else if ($val instanceof \DateTime) {
                    $val = $val->format('c');
                } else if ($val instanceof StringType) {
                    $val = (string) $val;
                } else {
                    $val = (array) $val;
                    $bCastType = true;
                }
                if (($prefix != '' || isset($bCastType)) && !empty($val) && is_array($val)) {
                    $i = 1;
                    $val = array_combine(
                        array_map(function($k) use($prefix, $key, $member, &$i) {
                            return (empty($prefix) ? (ucfirst($key) . ($member ? '.member.' : '.')) : $prefix)
                                . (is_numeric($k) ? $i++ : ucfirst($k));
                        }, array_keys($val)),
                        array_values($val)
                    );
                }
            } else if (is_int($val) || is_float($val)) {
                $val = (string) $val;
            } else if (is_bool($val)) {
                $val = $val ? 'true' : 'false';
            }
            return $val;
        };
        $props = $this->getReflectionClass()->getProperties(\ReflectionProperty::IS_PUBLIC);
        /* @var $prop \ReflectionProperty */
        foreach ($props as $prop) {
            $val = $trait($prop->getName(), $prop->getValue($this));
            if ($val === null) continue;
            if (is_array($val)) {
                $arr = array_merge($arr, $val);
            } else {
                $arr[$prefix . $this->uppercaseProperty($prop->getName())] = $val;
            }
        }
        //Passes through an internal properties as well
        foreach ($this->_properties as $prop) {
            if (isset($this->propertiesData[$prop])) {
                $val = $trait($prop, $this->propertiesData[$prop]);
            } else {
                $val = null;
                continue;
            }
            if (is_array($val)) {
                $arr = array_merge($arr, $val);
            } else {
                $arr[$prefix . $this->uppercaseProperty($prop)] = $val;
            }
        }
        return array_filter($arr, function($v){
            return $v !== null;
        });
    }

    /**
     * Gets query parameters array without member prefix
     *
     * @param   string    $uriParameterName  optional Parameter name.
     * @return  string    Returns query parameters array looks like array ( '[parameterName.][propName[.n]]' => value )
     *                    Values are not url encoded.
     */
    public function getQueryArrayBare($uriParameterName = null)
    {
        return $this->getQueryArray($uriParameterName, false);
    }

    /**
     * Gets uppercased property
     *
     * @param    string    $property
     * @return   string    Returns uppercased property
     */
    protected function uppercaseProperty($property)
    {
        if (preg_match("/^(ssl)/", $property, $m)) {
            $ret = strtoupper($m[1]) . substr($property, strlen($m[1]));
        } else {
            $ret = ucfirst($property);
        }
        return $ret;
    }

    /**
     * This method is for internal usage only
     *
     * @return bool
     */
    public function _getServiceRelatedDatasetUpdated()
    {
        return $this->serviceRelatedDatasetUpdated;
    }

    /**
     * This method is for internal usage only
     *
     * @param   bool   $updated
     * @return  AbstractDataType
     */
    public function _setServiceRelatedDatasetUpdated($updated)
    {
        $this->serviceRelatedDatasetUpdated = (bool) $updated;
        return $this;
    }

    /**
     * This method is for internal usage only
     *
     * @return bool
     */
    public function _getExternalKeysUpdated()
    {
        return $this->externalKeysUpdated;
    }

    /**
     * This method is for internal usage only
     *
     * @param   bool   $updated
     * @return  AbstractDataType
     */
    public function _setExternalKeysUpdated($updated)
    {
        $this->externalKeysUpdated = (bool) $updated;
        return $this;
    }

    /**
     * Returns true if inherited data is set.
     *
     * @return  bool Returns true if inherited data is set.
     */
    public function _hasInheritedDataSet()
    {
        return $this->_getExternalKeysUpdated() && $this->_getServiceRelatedDatasetUpdated();
    }
}