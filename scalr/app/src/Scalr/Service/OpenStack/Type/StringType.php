<?php
namespace Scalr\Service\OpenStack\Type;

/**
 * Abstract String Type
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    06.12.2012
 */
abstract class StringType
{
    /**
     * This method is supposed to be overridden
     *
     * @return string Returns contant prefix which should be taken into account
     *                to evaluate list of allowed values.
     */
    protected static function getPrefix()
    {
        return 'VAL_';
    }

    /**
     * Array of allowed values
     * @var array
     */
    private static $allowed;

    /**
     * Value
     * @var string
     */
    private $value;

    /**
     * Constructor
     *
     * @param   string      $value  An application value
     * @throws  \InvalidArgumentException
     */
    public function __construct($value)
    {
        $this->set($value);
    }

    /**
     * Sets value
     *
     * @param   string   $value  An value for the object
     * @throws  \InvalidArgumentException
     */
    public function set($value)
    {
        $this->value = (string) $value;
        if (!$this->validate()) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid value "%s". Allowed values are: "%s"', $this->value, join('", "', self::getAllowedValues())
            ));
        }
    }

    /**
     * Gets value
     *
     * @return string Returns an application value
     */
    public function get()
    {
        return $this->value;
    }

    public static function __callStatic($name, $args)
    {
        $class = get_called_class();
        $czName = $class::getPrefix() . strtoupper(preg_replace('/(?!^)[[:upper:]]+/', '_$0', $name));
        $allowed = $class::getAllowedValues();
        if (array_key_exists($czName, $allowed)) {
            return new $class ($allowed[$czName]);
        }
        throw new \BadMethodCallException(sprintf('Unknown static method "%s" for the class %s', $name, $class));
    }

    /**
     * Gets allowed values for the class
     *
     * @return  array Returns array of the allowed values looks like array(CONST_NAME => value)
     */
    public static function getAllowedValues()
    {
        $class = get_called_class();
        if (!isset(self::$allowed[$class])) {
            self::$allowed[$class] = array();
            $ref = new \ReflectionClass($class);
            $prefix = $class::getPrefix();
            $len = strlen($prefix);
            foreach ($ref->getConstants() as $cname => $cvalue) {
                if ($len && substr($cname, 0, $len) !== $prefix) continue;
                self::$allowed[$class][$cname] = $cvalue;
            }
        }
        return self::$allowed[$class];
    }

    /**
     * Validates result.
     *
     * @return  bool    Returns TRUE if value is valid or FALSE otherwise
     */
    private function validate()
    {
        if (!in_array($this->value, $this::getAllowedValues())) {
            return false;
        }
        return true;
    }

    /**
     * Returns string value
     */
    public function __toString()
    {
        return $this->get();
    }
}