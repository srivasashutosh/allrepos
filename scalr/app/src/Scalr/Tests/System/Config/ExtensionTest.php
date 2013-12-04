<?php

namespace Scalr\Tests\System\Config;

use Scalr\System\Config\Extension;
use Scalr\Tests\TestCase;

/**
 * Extension test
 *
 * @author    Vitaliy Demidov   <vitaliy@scalr.com>
 * @since     11.06.2013
 */
class ExtensionTest extends TestCase
{

    /**
     * Gets loaded extension instance
     *
     * @return Extension
     */
    protected function loadExtension()
    {
        $ext = new Extension();
        $ext->load();

        return $ext;
    }
    /**
     * @test
     */
    public function testLoad()
    {
        $ext = $this->loadExtension();

        $this->assertEquals('mysqli', $ext('scalr.connections.mysql.driver')->default);
        $this->assertEquals(null, $ext('scalr.connections.ldap.port')->default);
        $this->assertEquals(true, $ext('scalr.phpunit.skip_functional_tests')->default);

        $vars = $this->getConfigUsedVars();
        foreach ($vars as $varname => $info) {
            $this->assertTrue(($ext->defined($varname) ?: isset($ext->paths[$varname])), sprintf(
                "Constant '%s' which is used in file '%s' line '%s' is not defined in Extension class.",
                $varname, $info[0]['file'], $info[0]['line']
            ));
        }
    }

    /**
     * Scans all code searching usage of the config parameters
     *
     * @return array Returns array with dot notation access name keys
     */
    protected function getConfigUsedVars()
    {
        $root = realpath(APPPATH . '/..');
        $vars = array();
        $i = 0;
        //All php files which do not end with Test.php
        $iterator = new \RegexIterator(
            new \RecursiveIteratorIterator((new \RecursiveDirectoryIterator($root))),
            '/(?<!Test)\.php$/'
        );
        /* @var $fileInfo \SplFileInfo */
        foreach ($iterator as $fileInfo) {
            $content = file_get_contents($fileInfo->getRealPath());
            if (preg_match_all('/^.*?(?:config|config->get)\([\s\R]*[\'"](scalr[\w\.]+)[\'"][\s\R]*\).*$/m', $content, $m)) {
                foreach ($m[1] as $i => $constname) {
                    if (!isset($vars[$constname])) {
                        $vars[$constname] = array();
                    }
                    $vars[$constname][] = array(
                        'file' => str_replace(array($root . DIRECTORY_SEPARATOR, '\\'), array('', '/'), $fileInfo->getRealPath()),
                        'line' => trim($m[0][$i]),
                    );
                }
            }
        }

        return $vars;
    }

}
