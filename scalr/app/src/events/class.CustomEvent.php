<?php

class CustomEvent extends Event
{

    public $DBServer;
    private $eventName;
    public $params;

    public function __construct(DBServer $DBServer, $eventName, array $params)
    {
        parent::__construct();
        $this->eventName = $eventName;

        foreach ($params as $k => $v)
            $this->params[$this->underScope($k)] = $v;

        $this->DBServer = $DBServer;
    }

    /**
     * Returns event name
     *
     * @return string
     */
    public function GetName()
    {
        return $this->eventName;
    }

    protected function underScope ($name) {
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
}
