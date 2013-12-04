<?php

namespace Scalr\Logger\AuditLog;

/**
 * KeyValue Record
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    12.02.2012
 */
class KeyValueRecord
{

    /**
     * Datatype
     *
     * @var string
     */
    private $_objectDatatype;

    /**
     * Constructor
     *
     * @param  string   $datatype A class name which this object is based on.
     */
    public function __construct($datatype)
    {
        $this->_objectDatatype = $datatype;
    }

    /**
     * Gets a class name which this object is based on
     *
     * @return  string Returns the class name which this object is based on.
     */
    public function getObjectDataType()
    {
        return $this->_objectDatatype;
    }

    /**
     * Sets change
     *
     * @param   string     $property  The property name.
     * @param   string     $from      The value before the change.
     * @param   string     $to        The value after the change.
     * @return  KeyValueRecord
     */
    public function setState($property, $from, $to)
    {
        $this->$property = array(
            'old_value' => $from,
            'new_value' => $to,
        );
        return $this;
    }

    /**
     * Gets the change
     *
     * @param   string        $property  The property name.
     * @return  object|null   Returns object with two properties $obj->from and $obj->to.
     *                        If there are no change it returns null.
     */
    public function getState($property)
    {
        if (!property_exists($this, $property)) return null;
        $obj = new \stdClass();
        $obj->from = isset($this->$toProperty['old_value']) ? $this->$toProperty['old_value'] : null;
        $obj->to = isset($this->$toProperty['new_value']) ? $this->$toProperty['new_value'] : null;
        return $obj;
    }
}