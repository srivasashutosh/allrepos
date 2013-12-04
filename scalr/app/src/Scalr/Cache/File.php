<?php

class Scalr_Cache_File implements Scalr_Cache_Interface
{
    private function getFilePath($key)
    {
        return CACHEPATH . "/scalr_cache_file.{$key}.cache";
    }

    public function get($key)
    {
        return null;
    }

    public function check($key)
    {
        return false;
    }

    public function set($key, $value, $expire)
    {
        return true;
    }
}
