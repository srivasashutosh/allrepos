<?php

namespace Scalr\System\Config;

/**
 * Config loader
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    14.06.2013
 */
class Loader
{
    const YAML_FILE = SCALR_CONFIG_FILE;
    const YAML_CACHE_FILE = SCALR_CONFIG_CACHE_FILE;

    /**
     * Loads config
     *
     * @return  Yaml
     * @throws  Exception\LoaderException
     */
    public function load()
    {
        if (!file_exists(self::YAML_CACHE_FILE)) {
            $config = $this->refreshCache();
        } else if (!is_readable(self::YAML_CACHE_FILE)) {
            throw new Exception\LoaderException(sprintf(
                'Could not read config file from cache %s. Please check file permissions.',
                self::YAML_CACHE_FILE
            ));
        } else {
            $config = unserialize(file_get_contents(self::YAML_CACHE_FILE));
            $refresh = false;
            //Checks, if the files need to be refreshed.
            foreach ($config->getImports(true) as $path => $time) {
                if (!file_exists($path) || is_readable($path) && filemtime($path) > $time) {
                    $refresh = true;
                    break;
                }
            }
            if ($refresh) {
                $config = $this->refreshCache();
            }
        }
        return $config;
    }

    /**
     * Re-reads config and refreshes cache.
     *
     * @return  Yaml
     * @throws  Exception\LoaderException
     */
    protected function refreshCache()
    {
        $ext = new Extension();
        $ext->load();

        try {
            $yaml = Yaml::load(self::YAML_FILE);
        } catch (\Exception $e) {
            throw new Exception\LoaderException(sprintf(
                'Could not load config. %s',
                $e->getMessage()
            ));
        }

        $refSet = new \ReflectionMethod($yaml, 'set');
        $refSet->setAccessible(true);

        $before = clone $yaml;

        foreach ($ext as $key => $obj) {
            if (property_exists($obj, 'default') && (!$yaml->defined($key) || $yaml($key) === null)) {
                //Set defaults only in the case it is provided in the Extension.
                //Set defaults only if they are not overriden in config and not null.
                $refSet->invoke($yaml, $key, $obj->default);
            }
            //Checks if at least one from all parents is not required.
            $token = $key;
            while (strpos($token, '.')) {
                $token = preg_replace('/\.[^\.]+$/', '', $token);
                //Parent bag is not required
                if (!$ext->defined($token)) {
                    //And it is not defined in config
                    if (!$before->defined($token)) continue 2;
                    else {
                        //check presence of nodes if it is defined in config
                        break;
                    }
                }
            }
            if (!$yaml->defined($key)) {
                //If, after all, value has not been defined in the Extension, it is considered as user error.
                throw new Exception\LoaderException(sprintf(
                    'Parameter "%s" must be defined in the config', $key
                ));
            }
        }

        unset($before);

        //serialize yaml
        file_put_contents(self::YAML_CACHE_FILE, serialize($yaml));
        @chmod(self::YAML_CACHE_FILE, 0666);

        return $yaml;
    }
}