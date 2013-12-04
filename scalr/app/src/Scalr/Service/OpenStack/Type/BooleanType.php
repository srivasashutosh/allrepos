<?php
namespace Scalr\Service\OpenStack\Type;

/**
 * Boolean Type
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    08.05.2013
 */
class BooleanType
{

    /**
     * Boolean value
     *
     * @var bool
     */
    private $value;

    /**
     * Constructor
     * @param   string|bool|int   $value
     */
    public function __construct($value)
    {
        $this->set($value);
    }

    /**
     * Sets boolean value
     *
     * @param   string|int|bool   $value
     * @return  BooleanType
     */
    public function set($value)
    {
        $this->value = self::cast($value);
        return $this;
    }

    /**
     * Gets a value
     *
     * @return  bool Returns boolean value
     */
    public function get()
    {
        return $this->value;
    }

    /**
     * Initializes a new BooleanType object
     *
     * @param   string|bool|int $value Boolean value
     * @return  BooleanType
     */
    public static function init($value)
    {
        return new self($value);
    }

    /**
     * Type-casting the value
     *
     * 1 => true
     * 0 => false
     * "1" => true
     * "0" => false
     * ""  => false
     * true => true
     * false => false
     * "true" => true
     * "false" => false
     *
     * @param   string|int|bool $value Boolean value to cast
     * @return  bool Returns boolean value
     */
    public static function cast($value)
    {
        if (!is_bool($value)) {
            if (is_numeric($value)) {
                $value = !empty($value);
            } else if (is_string($value)) {
                $value = strtolower($value) === 'true';
            } else {
                $value = (bool)$value;
            }
        }

        return $value;
    }

    /**
     * Gets a boolean as string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->value ? 'true' : 'false';
    }
}