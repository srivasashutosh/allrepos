<?php

class Scalr_Net_Dns_ARecord extends Scalr_Net_Dns_Record
{

    public $name;
    public $ip;
    public $ttl;
    public $type = "A";

    const DEFAULT_TEMPLATE = "{name} {ttl} IN A {ip}";

    /**
     * Constructor
     *
     * @param string $name
     * @param string $ip
     * @param integer $ttl
     */
    function __construct($name, $value, $ttl = false)
    {
        parent::__construct();

        // Name
        if (($this->validator->validateRegexp($name, self::PAT_NON_FDQN) === true ||
            $name == "@" ||
            $name === "" ||
            $name == "*" ||
            $this->validator->validateRegexp($name, '/^\*\.[A-Za-z0-9]+[A-Za-z0-9\-\.]+[A-Za-z]+\.$/') === true ||
            $this->validator->validateDomain($name) === true) &&
            $this->validator->validateIp(rtrim($name, ".")) !== true )
            $this->name = $name;
        else
            throw new Scalr_Net_Dns_Exception(sprintf(_("'%s' is not a valid name for A record"), $name));

        if ($this->validator->validateIp($value) !== true)
            throw new Scalr_Net_Dns_Exception(sprintf(_("'%s' is not a valid value address for A record"), $value));
        else
            $this->ip = $value;

        $this->ttl = $ttl;
    }

    function generate()
    {
        $tags = array(
            "{name}"	=> $this->name,
            "{ttl}"		=> $this->ttl,
            "{ip}"		=> $this->ip
        );

        return str_replace(
            array_keys($tags),
            array_values($tags),
            self::DEFAULT_TEMPLATE
        );
    }
}

