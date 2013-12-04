<?php

class Scalr_Messaging_JsonSerializer {
    const SERIALIZE_BROADCAST = 'serializeBroadcast';

    private $msgClassProperties = array();

    static private $instance;

    /**
     * @return Scalr_Messaging_XmlSerializer
     */
    static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Scalr_Messaging_JsonSerializer();
        }
        return self::$instance;
    }

    function __construct () {
        $this->msgClassProperties = array_keys(get_class_vars('Scalr_Messaging_Msg'));
    }

    function serialize (Scalr_Messaging_Msg $msg, $options = array()) {
        $retval = new stdClass();
        $retval->name = $msg->getName();
        $retval->id = $msg->messageId;
        $retval->body = new stdClass();
        $retval->meta = array();

        $meta = (array)$msg->meta;
        unset($msg->meta);

        $this->walkSerialize($msg, $retval->body);
        $this->walkSerialize($meta, $retval->meta);

        return @json_encode($retval);
    }

    private function walkSerialize ($object, $retval) {
        foreach ($object as $k=>$v) {
            if (is_object($v) || is_array($v)) {
                $this->walkSerialize($v, $retval->{$this->underScope($k)});
            } else {
                if (is_object($object))
                   $retval->{$this->underScope($k)} = $v;
                else
                   $retval[$this->underScope($k)] = $v;
            }
        }
    }

    /**
     * @param string $xmlString
     * @return Scalr_Messaging_Msg
     */
    function unserialize ($jsonString) {
        $msg = @json_decode($jsonString);

        $ref = new ReflectionClass(Scalr_Messaging_Msg::getClassForName($msg->name));
        $retval = $ref->newInstance();
        $retval->messageId = "{$msg->id}";

        $this->walkUnserialize($msg->meta, $retval->meta);
        $this->walkUnserialize($msg->body, $retval);

        return $retval;
    }

    private function walkUnserialize ($msg, $retval) {
        foreach ($msg as $k=>$v) {
            if (is_object($v) || is_array($v)) {
                $this->walkUnserialize($v, $retval->{$this->camelCase($k)});
            } else {
                if (is_object($msg))
                   $retval->{$this->camelCase($k)} = $v;
                else
                   $retval[$this->camelCase($k)] = $v;
            }
        }
    }

    private function underScope ($name) {
        $parts = preg_split("/[A-Z]/", $name, -1, PREG_SPLIT_OFFSET_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $ret = "";
        foreach ($parts as $part) {
            if ($part[1]) {
                $ret .= "_" . strtolower($name{$part[1]-1});
            }
            $ret .= $part[0];
        }
        return $ret;
    }

    private function camelCase ($name) {
        $parts = explode("_", $name);
        $first = array_shift($parts);
        return $first . join("", array_map("ucfirst", $parts));
    }
}