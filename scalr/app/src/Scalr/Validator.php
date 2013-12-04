<?php

class Scalr_Validator
{
    const REGEX = 'regex';
    const MINMAX = 'minmax';
    const RANGE = 'range';
    const REQUIRED = 'required';
    const NOHTML = 'nohtml';
    const EMAIL = 'email';
    const IP = 'ip';
    const DOMAIN = 'domain';
    const NOEMPTY = 'notEmpty';

    protected $errors = array();

    public function validate($value, $validators)
    {
        $result = true;
        $type = gettype($value);
        foreach ($validators as $key => $validator) {
            $method = "validate" . ucfirst($key);
            $resultValidator = $this->{$method}($value, $type, $validator);
            if ($resultValidator !== true && $result === true) {
                $result = array();
                $result = array_merge($result, $resultValidator);
            } else if ($resultValidator !== true && $result !== true) {
                $result = array_merge($result, $resultValidator);
            }
        }

        return $result;
    }

    public function validateRegex($value, $type, $options)
    {
        return true;
    }

    public function validateMinmax($value, $type, $options)
    {
        if ($type == 'string') {
            $len = strlen($value);
            if (isset($options['min']) && $options['min'] > $len)
                return array("Value must be longer then {$options['min']} chars");

            if (isset($options['max']) && $options['max'] < $len)
                return array("Value must be shorter then {$options['max']} chars");

            return true;

        } else if ($type == 'integer') {
            if (isset($options['min']) && $options['min'] > $value)
                return array("Value must be greater then {$options['min']}");

            if (isset($options['max']) && $options['max'] < $value)
                return array("Value must be lower then {$options['max']}");

            return true;

        } else {
            return true;
        }
    }

    public function validateRequired($value, $type, $options)
    {
        if ($options === true && !$value)
            return array("Value is required");
        else
            return true;
    }

    public function validateRange($value, $type, $options)
    {
        if (($type == 'string' || $type == 'integer') && $value && is_array($options)) {
            if (! in_array($value, $options))
                return array('Not allowed value');
            else
                return true;
        } else {
            return true;
        }
    }

    public function validateIp($value, $type = null, $options = null)
    {
        $version = isset($options['version']) ? $options['version'] : 4;

        switch ($version) {
            case 4 :
                $flag = FILTER_FLAG_IPV4;
                break;
            case 6 :
                $flag = FILTER_FLAG_IPV6;
                break;
            default:
                $flag = null;
        }

        return filter_var($value, FILTER_VALIDATE_IP, $flag) !== false ?:
               array('This is not a valid IP address.');
    }

    public function validateNohtml($value, $type, $options)
    {
        if ($options === true && preg_match('/^[A-Za-z0-9-\s]+$/si', $value))
            return true;
        else
            return array('Value should contain only letters and numbers');
    }

    public function validateEmail($value, $type = null, $options = null)
    {
        if ($options === true && $value) {
            if (filter_var($value, FILTER_VALIDATE_EMAIL) !== false)
                return true;
            else
                return array('Value should be valid email address');
        } else {
            return true;
        }
    }

    public function validateDomain($value, $type = null, $options = null)
    {
        $allowed_utf8_chars = isset($options['allowed']) ? preg_quote($options['allowed']) : '';
        $disallowed_utf8_chars = isset($options['disallowed']) ? preg_quote($options['disallowed']) : '';

        $value = rtrim($value, ".");

        $retval = (bool) preg_match(
            '/^([a-zA-Z0-9' . $allowed_utf8_chars
          . ']+[a-zA-Z0-9\-' . $allowed_utf8_chars
          . ']*\.[a-zA-Z0-9' . $allowed_utf8_chars . ']*?)+$/usi', $value);

        if ($disallowed_utf8_chars != '') {
            $retval = $retval && !((bool)preg_match("/[" . $disallowed_utf8_chars . "]+/siu", $value));
        }

        return $retval ?: array('This is not a valid domain.');
    }


    public function validateRegexp($value, $pattern)
    {
        $retval = (bool) preg_match($pattern, $value);

        return $retval ?: array('This value does not match a pattern.');
    }

    public function validateNotEmpty($value, $type = null, $options = null)
    {
        return !empty($value) ?: array('This value should be provided.');
    }
}
