<?php

namespace Scalr\Tests\Functional\Ui\Controller;

use Scalr\Service\Aws\Ec2\DataType\AvailabilityZoneFilterNameType;
use Scalr\Tests\WebTestCase;
use \CONFIG;

/**
 * Functional test for the Scalr_UI_Controller_Account2_Environments_Platform class.
 *
 * @author   Vitaliy Demidov   <vitaliy@scalr.com>
 * @since    05.03.2013
 */
class PlatformTest extends WebTestCase
{

    /**
     * Gets platform config value
     *
     * @param   string    $name  Variable name
     * @param   bool      $enc   optional
     * @param   string    $group optional
     * @return  mixed     Returns platform config value value
     */
    private function getPlatformConfigValue($name, $enc = true, $group = '')
    {
        $val = $this->getEnvironment()->getPlatformConfigValue($name, $enc, $group);
        return $val;
    }

    /**
     * Resets environment cache
     */
    private function resetEnvironmentCache()
    {
        $refCache = new \ReflectionProperty('Scalr_Environment', 'cache');
        $refCache->setAccessible(true);
        $refCache->setValue($this->getEnvironment(), array());
    }

    /**
     * DataProvider method for the testPlatformAction
     */
    public function providerPlatformAction()
    {
        $aPrefixed = array_fill_keys(array(
            \SERVER_PLATFORMS::CLOUDSTACK, \SERVER_PLATFORMS::IDCF, \SERVER_PLATFORMS::UCLOUD,
            \SERVER_PLATFORMS::OPENSTACK, \SERVER_PLATFORMS::RACKSPACENG_UK, \SERVER_PLATFORMS::RACKSPACENG_US,
        ), true);
        $pars = array();
        foreach (\SERVER_PLATFORMS::GetList() as $platform => $opts) {
            if ($platform == \SERVER_PLATFORMS::RACKSPACE) continue;
            if ($platform == \SERVER_PLATFORMS::EUCALYPTUS) continue;
            $pars[] = array($platform, (array_key_exists($platform, $aPrefixed) ? $platform . '.' : ''));
        }
        return $pars;
    }

    /**
     * DataProvider method for the testPlatformGroupAction
     */
    public function providerPlatformGroupAction()
    {
        return array(
            array(\SERVER_PLATFORMS::RACKSPACE, ''),
            array(\SERVER_PLATFORMS::EUCALYPTUS, ''),
        );
    }

    /**
     * @test
     * @dataProvider  providerPlatformAction
     */
    public function testPlatformAction($platform, $prefix)
    {
        $content = $this->request('/account/environments/' . $this->_testEnvId . '/platform/' . $platform);
        $this->resetEnvironmentCache();
        if (!empty($content['moduleParams']['params'])) {
            foreach ($content['moduleParams']['params'] as $varname => $val) {
                if (!preg_match('/\.is_enabled$/', $varname)) {
                    $varname = $prefix . $varname;
                    $enc = true;
                } else {
                    $enc = false;
                }
                if (is_string($val) && ($val === 'Uploaded' || preg_match("/^\\*+$/", $val))) {
                    $val = $this->getEnvironment()->getPlatformConfigValue($varname);
                    $this->resetEnvironmentCache();
                }
                $this->assertEquals(
                    (is_bool($val) ? ($val ? 1 : 0) : $val),
                    $this->getPlatformConfigValue($varname, $enc),
                    sprintf('Values for variable %s are not equal', $varname), 0, 10, true
                );
            }
        } else {
            $this->markTestSkipped(sprintf('Environment for the "%s" platform has not been activated.', $platform));
        }
    }

    /**
     * @test
     * @dataProvider providerPlatformGroupAction
     */
    public function testPlatformGroupAction($platform, $prefix = '')
    {
        $content = $this->request('/account/environments/' . $this->_testEnvId . '/platform/' . $platform);
        $this->resetEnvironmentCache();
        if (!empty($content['moduleParams']['params'])) {
            foreach ($content['moduleParams']['params'] as $group => $groupData) {
                $this->resetEnvironmentCache();
                foreach ($groupData as $varname => $val) {
                    if (is_string($val) && ($val === 'Uploaded' || preg_match("/^\\*+$/", $val))) {
                        $val = $this->getEnvironment()->getPlatformConfigValue($varname, true, $group);
                        $this->resetEnvironmentCache();
                    }
                    $this->assertEquals(
                        (is_bool($val) ? ($val ? 1 : 0) : $val),
                        $this->getPlatformConfigValue($varname, true, $group),
                        sprintf('Values for variable %s are not equal', $varname), 0, 10, true
                    );
                }
            }
        }
    }
}