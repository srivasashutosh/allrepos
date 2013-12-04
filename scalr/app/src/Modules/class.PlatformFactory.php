<?php

class PlatformFactory
{
    private static $cache = array();

    /**
     * Create platform instance
     * @param string $platform
     * @return IPlatformModule
     */
    public static function NewPlatform($platform)
    {
        if (!array_key_exists($platform, self::$cache)) {
            if ($platform == SERVER_PLATFORMS::EC2)
                self::$cache[$platform] = new Modules_Platforms_Ec2();
            elseif ($platform == SERVER_PLATFORMS::GCE)
                self::$cache[$platform] = new Modules_Platforms_GoogleCE();
            elseif ($platform == SERVER_PLATFORMS::EUCALYPTUS)
                self::$cache[$platform] = new Modules_Platforms_Eucalyptus();
            elseif ($platform == SERVER_PLATFORMS::RACKSPACE)
                self::$cache[$platform] = new Modules_Platforms_Rackspace();
            elseif ($platform == SERVER_PLATFORMS::NIMBULA)
                self::$cache[$platform] = new Modules_Platforms_Nimbula();
            elseif ($platform == SERVER_PLATFORMS::CLOUDSTACK)
                self::$cache[$platform] = new Modules_Platforms_Cloudstack();
            elseif ($platform == SERVER_PLATFORMS::IDCF)
                self::$cache[$platform] = new Modules_Platforms_Idcf();
            elseif ($platform == SERVER_PLATFORMS::UCLOUD)
                self::$cache[$platform] = new Modules_Platforms_uCloud();

            elseif ($platform == SERVER_PLATFORMS::OPENSTACK)
                self::$cache[$platform] = new Modules_Platforms_Openstack();
            elseif ($platform == SERVER_PLATFORMS::RACKSPACENG_UK)
                self::$cache[$platform] = new Modules_Platforms_RackspaceNgUk();
            elseif ($platform == SERVER_PLATFORMS::RACKSPACENG_US)
                self::$cache[$platform] = new Modules_Platforms_RackspaceNgUs();
            else
                throw new Exception(sprintf("Platform %s not supported by Scalr", $platform));
        }

        return self::$cache[$platform];
    }
}
