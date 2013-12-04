<?php

class Scalr_Net_Dns_CNAMERecord extends Scalr_Net_Dns_Record
{

    public $name;
    public $cname;
    public $ttl;
    public $type;

    const DEFAULT_TEMPLATE = "{name} {ttl} IN CNAME {cname}";

    /**
     * Constructor
     *
     * @param string $name
     * @param string $rname
     * @param integer $ttl
     * @param string $class
     */
    function __construct($name, $value, $ttl = false)
    {
        parent::__construct();

        $this->type = "CNAME";

        // Name
        if ($this->validator->validateRegexp($name, self::PAT_NON_FDQN) === true ||
            $this->validator->validateRegexp($name, '/^\*\.[A-Za-z0-9\-\.]+[A-Za-z0-9]+$/si') === true ||
            //default._domainkey.vip
            $this->validator->validateRegexp($name, '/^[_A-Za-z0-9]+[_\.A-Za-z0-9-]*[A-Za-z0-9]+[\.]*$/si') === true ||
            $this->validator->validateDomain($name) === true &&
            $this->validator->validateIp(rtrim($name, ".")) !== true || $name == "*")
            $this->name = $name;
        else
            throw new Scalr_Net_Dns_Exception(sprintf(_("'%s' is not a valid name for CNAME record"), $name));

        // cname
        if ($this->validator->validateDomain($value) !== true) {
            if ($this->validator->validateRegexp($value, self::PAT_NON_FDQN) === true ||
                $this->validator->validateRegexp($value, '/^[_A-Za-z0-9]+[_\.A-Za-z0-9-]*[A-Za-z0-9]+[\.]*$/si') === true)
                $this->cname = $value;
            else
                 throw new Scalr_Net_Dns_Exception(sprintf(_("'%s' is not a valid value for CNAME record"), $value));
        }
        else
            $this->cname = $this->dottify($value);

        $this->ttl = $ttl;
    }

    /**
     * __ToString Magic function
     *
     * @return string
     */
    function generate()
    {
        $tags = array(
            "{name}"	=> $this->name,
            "{ttl}"		=> $this->ttl,
            "{cname}"	=> $this->cname
        );

        return str_replace(
            array_keys($tags),
            array_values($tags),
            self::DEFAULT_TEMPLATE
        );
    }
}

