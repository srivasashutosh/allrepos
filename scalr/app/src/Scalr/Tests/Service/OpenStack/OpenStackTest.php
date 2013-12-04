<?php
namespace Scalr\Tests\Service\OpenStack;

use Scalr\Service\OpenStack\Services\Network\Type\CreatePort;
use Scalr\Service\OpenStack\Services\Network\Type\AllocationPool;
use Scalr\Service\OpenStack\Services\Servers\Type\Network;
use Scalr\Service\OpenStack\Services\Servers\Type\NetworkList;
use Scalr\Service\OpenStack\Services\Servers\Type\Personality;
use Scalr\Service\OpenStack\Services\Servers\Type\PersonalityList;
use Scalr\Service\OpenStack\Services\Volume\Type\VolumeStatus;
use Scalr\Service\OpenStack\Exception\OpenStackException;
use Scalr\Service\OpenStack\Services\Servers\Type\ServersExtension;
use Scalr\Service\OpenStack\Exception\RestClientException;
use Scalr\Service\OpenStack\Services\Servers\Type\ListServersFilter;
use Scalr\Service\OpenStack\Services\Servers\Type\ImageStatus;
use Scalr\Service\OpenStack\Services\Servers\Type\ListImagesFilter;
use Scalr\Service\OpenStack\OpenStackConfig;
use Scalr\Service\OpenStack\Type\AppFormat;
use Scalr\Service\OpenStack\OpenStack;

/**
 * OpenStack TestCase
 *
 * @author   Vitaliy Demidov  <vitaliy@scalr.com>
 * @since    05.12.2012
 */
class OpenStackTest extends OpenStackTestCase
{

    const VOLUME_SIZE = 100;

    const NAME_NETWORK = 'net';

    const NAME_SUBNET = 'subnet';

    const NAME_PORT = 'port';

    /**
     * Provider of the instances for the functional tests
     */
    public function providerRs()
    {
        return array(
            //Enter.It Grizzly
            array(\SERVER_PLATFORMS::OPENSTACK, 'EnterIt', 'cb457ffd-469e-4fae-9bb0-3f618e69d74f'),

//             //Nebula
//             array(\SERVER_PLATFORMS::OPENSTACK, 'RegionOne', '07b26892-9716-453f-9443-9b5e90d2c978'),

//             //Open Cloud System
//             array(\SERVER_PLATFORMS::OPENSTACK, 'RegionOne', '7a0d5ff5-efa1-4dae-a18e-0238fe27f287'),

            //Rackspace US
            array(\SERVER_PLATFORMS::RACKSPACENG_US, 'DFW', '3afe97b2-26dc-49c5-a2cc-a2fc8d80c001'),
            //Rackspase UK
            array(\SERVER_PLATFORMS::RACKSPACENG_UK, 'LON', '3afe97b2-26dc-49c5-a2cc-a2fc8d80c001'),
        );
    }

    /**
     * Gets test server name
     *
     * @param   string $suffix optional Name suffix
     * @return  string Returns test server name
     */
    public static function getTestServerName($suffix = '')
    {
        return self::getTestName('server' . (!empty($suffix) ? '-' . $suffix : ''));
    }

    /**
     * Gets test volume name
     *
     * @param   string $suffix optional Name suffix
     * @return  string Returns test volume name
     */
    public static function getTestVolumeName($suffix = '')
    {
        return self::getTestName('volume' . (!empty($suffix) ? '-' . $suffix : ''));
    }

    /**
     * Gets test snapshot name
     *
     * @param   string $suffix optional Name suffix
     * @return  string Returns test snapshot name
     */
    public static function getTestSnapshotName($suffix = '')
    {
        return self::getTestName('snapshot' . (!empty($suffix) ? '-' . $suffix : ''));
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Tests\Service\OpenStack.OpenStackTestCase::setUp()
     */
    protected function setUp()
    {
        parent::setUp();
    }

    /**
     * {@inheritdoc}
     * @see Scalr\Tests\Service\OpenStack.OpenStackTestCase::tearDown()
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @test
     */
    public function testGetAvailableServices()
    {
        $avail = OpenStack::getAvailableServices();
        $this->assertNotEmpty($avail);
        $this->assertInternalType('array', $avail);
        $this->assertArrayHasKey('servers', $avail);
        $this->assertArrayNotHasKey('abstract', $avail);
    }

    /**
     * @test
     * @dataProvider providerRs
     */
    public function testFunctionalOpenStack($platform, $region, $imageId)
    {
        if ($this->isSkipFunctionalTests()) {
            $this->markTestSkipped();
        }
        /* @var $rs OpenStack */
        if ($this->getContainer()->environment->isPlatformEnabled($platform)) {
            $rs = $this->getContainer()->openstack($platform, $region);
//             $rs->getClient()->setDebug(true);
            $this->assertInstanceOf($this->getOpenStackClassName('OpenStack'), $rs);
        } else {
            //Environment has not been activated yet.
            $this->markTestSkipped(sprintf('Environment for the "%s" platform has not been activated.', $platform));
        }

        $os = $this->getContainer()->openstack($platform, 'INVALID-REGION-TEST');
        try {
            $ext = $os->servers->listExtensions();
            unset($os);
            $this->assertTrue(false, 'An exception must be thrown in this test');
        } catch (OpenStackException $e) {
            $this->assertTrue(true);
        }
        unset($os);

        //Activates rest client debug output
        $one = $rs->servers;
        $this->assertInstanceOf($this->getOpenStackClassName('Services\\ServersService'), $one);
        $two = $rs->servers;
        $this->assertInstanceOf($this->getOpenStackClassName('Services\\ServersService'), $two);
        $this->assertSame($one, $two, 'Service interface is expected to be cached within each separate OpenStack instance.');

        $aZones = $rs->listZones();
        $this->assertNotEmpty($aZones);
        unset($aZones);

        //List tenants test
        $tenants = $rs->listTenants();
        $this->assertNotEmpty($tenants);
        $this->assertTrue(is_array($tenants));
        unset($tenants);

        //Get Limits test
        $limits = $rs->servers->getLimits();
        $this->assertTrue(is_object($limits));
        unset($limits);

        $aExtensions = $rs->servers->listExtensions();
        $this->assertTrue(is_array($aExtensions));
        unset($aExtensions);

        $hasNetwork = $rs->hasService(OpenStack::SERVICE_NETWORK);

        if ($hasNetwork) {
            //Quantum API tests
            $testNetworkName = self::getTestName(self::NAME_NETWORK);
            $testSubnetName = self::getTestName(self::NAME_SUBNET);
            $testPortName = self::getTestName(self::NAME_PORT);

            //ListNetworks test
            $networks = $rs->network->networks->list(null, array(
                'status' => 'ACTIVE',
                'shared' => false
            ));
            $this->assertInternalType('array', $networks);
            if (isset($networks[0])) {
                $this->assertInternalType('object', $networks[0]);
                $this->assertNotEmpty($networks[0]->id);

                //Show Network test
                $network = $rs->network->networks->list($networks[0]->id);
                $this->assertEquals($networks[0], $network);
                unset($network);
            }
            unset($networks);

            //ListSubnets test
            $subnets = $rs->network->subnets->list();
            $this->assertInternalType('array', $subnets);
            if (isset($subnets[0])) {
                $this->assertInternalType('object', $subnets[0]);
                $this->assertNotEmpty($subnets[0]->id);

                //Show Subnet test
                $subnet = $rs->network->subnets->list($subnets[0]->id);
                $this->assertEquals($subnets[0], $subnet);
                unset($subnet);
            }
            unset($subnets);

            //ListPorts test
            $ports = $rs->network->ports->list();
            $this->assertInternalType('array', $ports);
            if (isset($ports[0])) {
                $this->assertInternalType('object', $ports[0]);
                $this->assertNotEmpty($ports[0]->id);

                //Show Port test
                $port = $rs->network->ports->list($ports[0]->id);
                $this->assertEquals($ports[0], $port);
                unset($port);
            }
            unset($ports);

            //Tries to find the networks that were created recently by this test
            //but hadn't been removed at any reason.
            $networks = $rs->network->networks->list(null, array(
                'name' => $testNetworkName
            ));
            foreach ($networks as $network) {
                //Removes previously created networks
                $rs->network->networks->update($network->id, null, false);
                $rs->network->networks->delete($network->id);
            }
            unset($networks);

            //Tries to find the ports that were created recently by this test
            //but hadn't been removed at any reason.
            $ports = $rs->network->ports->list(null, array(
                'name' => array($testPortName, $testPortName . '1')
            ));
            foreach ($ports as $port) {
                //Removes previously created ports
                $rs->network->ports->delete($port->id);
            }
            unset($ports);

            //Tries to find the subnets that where created by this test but hadn't been removed yet.
            $subnets = $rs->network->subnets->list(null, array(
                'name' => array($testSubnetName, $testSubnetName . '1')
            ));
            $this->assertInternalType('array', $subnets);
            foreach ($subnets as $subnet) {
                //Removes previously created subnets
                $rs->network->subnets->delete($subnet->id);
            }

            //Creates new network
            $network = $rs->network->networks->create($testNetworkName, false, false);
            $this->assertInternalType('object', $network);
            $this->assertNotEmpty($network->id);
            $this->assertEquals(false, $network->admin_state_up);
            $this->assertEquals(false, $network->shared);

            //Updates newtork state
            $network = $rs->network->networks->update($network->id, null, true);
            $this->assertInternalType('object', $network);
            $this->assertEquals(true, $network->admin_state_up);

            //Creates subnet
            $subnet = $rs->network->subnets->create(array(
                'network_id'       => $network->id,
                //ip_version is set internally with 4, but you can provide it explicitly
                'cidr'             => '10.0.3.0/24',
                'name'             => $testSubnetName,
                'allocation_pools' => array(
                                          new AllocationPool('10.0.3.20', '10.0.3.22')
                                      ),
            ));
            $this->assertInternalType('object', $subnet);
            $this->assertEquals($testSubnetName, $subnet->name);
            $this->assertNotEmpty($subnet->id);
            $this->assertInternalType('array', $subnet->allocation_pools);
            $this->assertNotEmpty($subnet->allocation_pools);
            $this->assertEquals('10.0.3.22', $subnet->allocation_pools[0]->end);

            //Updates the subnet
            $subnet = $rs->network->subnets->update($subnet->id, array(
                'name' => $testSubnetName . '1'
            ));
            $this->assertInternalType('object', $subnet);
            $this->assertNotEmpty($subnet->name);
            $this->assertEquals($testSubnetName . '1', $subnet->name);

            //Removes subnet
            $ret = $rs->network->subnets->delete($subnet->id);
            $this->assertTrue($ret);

            //Creates port

            //Let's use object here
            $req = new CreatePort($network->id);
            $req->name = $testPortName;

            //You may pass object aw well as array
            $port = $rs->network->ports->create($req);
            $this->assertInternalType('object', $port);
            $this->assertEquals($network->id, $port->network_id);
            $this->assertEquals($testPortName, $port->name);
            $this->assertNotEmpty($port->id);

            //Updates port
            $port = $rs->network->ports->update($port->id, array(
                'name' => $testPortName . '1'
            ));
            $this->assertInternalType('object', $port);
            $this->assertEquals($testPortName . '1', $port->name);

            //Removes port
            $ret = $rs->network->ports->delete($port->id);
            $this->assertTrue($ret);

            //Removes created network
            $rs->network->networks->update($network->id, null, false);
            $ret = $rs->network->networks->delete($network->id);
            $this->assertTrue($ret);
            unset($network);

        }

        //List snapshots test
        $snList = $rs->volume->snapshots->list();
        $this->assertTrue(is_array($snList));
        foreach ($snList as $v) {
            if ($v->display_name == self::getTestSnapshotName()) {
                $rs->volume->snapshots->delete($v->id);
            }
        }
        unset($snList);

        //List Volume Types test
        $volumeTypes = $rs->volume->listVolumeTypes();
        $this->assertTrue(is_array($volumeTypes));
        foreach ($volumeTypes as $v) {
            $volumeTypeDesc = $rs->volume->getVolumeType($v->id);
            $this->assertTrue(is_object($volumeTypeDesc));
            unset($volumeTypeDesc);
            break;
        }

        //List Volumes test
        $aVolumes = $rs->volume->listVolumes();
        $this->assertTrue(is_array($aVolumes));
        foreach ($aVolumes as $v) {
            if ($v->display_name == self::getTestVolumeName()) {
                if (in_array($v->status, array(VolumeStatus::STATUS_AVAILABLE, VolumeStatus::STATUS_ERROR))) {
                    $ret = $rs->volume->deleteVolume($v->id);
                }
            }
        }

        //Create Volume test
        $volume = $rs->volume->createVolume(self::VOLUME_SIZE, self::getTestVolumeName());
        $this->assertTrue(is_object($volume));
        $this->assertNotEmpty($volume->id);

        for ($t = time(), $s = 1; (time() - $t) < 300 &&
            !in_array($volume->status, array(VolumeStatus::STATUS_AVAILABLE, VolumeStatus::STATUS_ERROR)); $s += 5) {
            sleep($s);
            $volume = $rs->volume->getVolume($volume->id);
            $this->assertTrue(is_object($volume));
            $this->assertNotEmpty($volume->id);
        }
        $this->assertContains($volume->status, array(VolumeStatus::STATUS_AVAILABLE, VolumeStatus::STATUS_ERROR));

//         //Create snapshot test
//         //WARNING! It takes too long time.
//         $snap = $rs->volume->snapshots->create($volume->id, self::getTestSnapshotName());
//         $this->assertTrue(is_object($snap));
//         $this->assertNotEmpty($snap->id);
//         for ($t = time(), $s = 1; (time() - $t) < 600 && !in_array($snap->status, array('available', 'error')); $s += 5) {
//             sleep($s);
//             $snap = $rs->volume->snapshots->get($snap->id);
//             $this->assertNotEmpty($snap->id);
//         }
//         $this->assertContains($snap->status, array('available', 'error'));

//         //Delete snapshot test
//         $ret = $rs->volume->snapshots->delete($snap->id);
//         $this->assertTrue($ret);
//         unset($snap);

//         sleep(5);

        //Delete Volume test
        $ret = $rs->volume->deleteVolume($volume->id);
        $this->assertTrue($ret);
        unset($volume);

        sleep(5);

        $pool = null;
        if ($rs->servers->isExtensionSupported(ServersExtension::floatingIpPools())) {
            $aFloatingIpPools = $rs->servers->listFloatingIpPools();
            $this->assertTrue(is_array($aFloatingIpPools));
            foreach ($aFloatingIpPools as $v) {
                $pool = $v->name;
                break;
            }
            $this->assertNotNull($pool);
            unset($aFloatingIpPools);
        }
        if ($rs->servers->isExtensionSupported(ServersExtension::floatingIps())) {
            $this->assertNotNull($pool);
            $aFloatingIps = $rs->servers->floatingIps->list();
            $this->assertTrue(is_array($aFloatingIps));
            foreach ($aFloatingIps as $v) {
                $r = $rs->servers->floatingIps->get($v->id);
                $this->assertTrue(is_object($r));
                break;
            }
            unset($aFloatingIps);

            //default pool for rackspase is 'nova'
            $fip = $rs->servers->floatingIps->create($pool);
            $this->assertTrue(is_object($fip));
            $r = $rs->servers->floatingIps->delete($fip->id);
            $this->assertTrue($r);
            try {
                //Verifies that ip has been successfully removed
                $res = $rs->servers->floatingIps->get($fip->id);
                $this->assertTrue(false, 'Exception must be thrown here');
            } catch (RestClientException $e) {
                if ($e->error->code == 404) {
                    $this->assertTrue(true);
                } else {
                    //OpenStack Grizzly fails with 500 error code.
                    //throw $e;
                }
            }
            unset($fip);
        }

        //List flavors test
        $flavorsList = $listFlavors = $rs->servers->listFlavors();
        $this->assertTrue(is_array($flavorsList));
        $flavorId = null;
        foreach ($flavorsList as $v) {
            $flavorId = $v->id;
            break;
        }
        $this->assertNotNull($flavorId);
        unset($flavorsList);

        //List servers test
        $ret = $rs->servers->list();
        $this->assertTrue(is_array($ret));
        if (!empty($ret)) {
            foreach ($ret as $v) {
                if ($v->name == self::getTestServerName() || $v->name == self::getTestServerName('renamed')) {
                    //Removes servers
                    try {
                        $rs->servers->deleteServer($v->id);
                    } catch (RestClientException $e) {
                        echo $e->getMessage() . "\n";
                    }
                }
            }
        }

        $personality = new PersonalityList(array(
            new Personality('/etc/scalr/private.d/.user-data', base64_encode('super data'))
        ));

        $netList = null;

        //Create server test
        $srv = $rs->servers->createServer(
            self::getTestServerName(), $flavorId, $imageId, null, null, $personality, $netList
        );
        $this->assertInstanceOf('stdClass', $srv);

        $srv = $rs->servers->getServerDetails($srv->id);
        $this->assertInstanceOf('stdClass', $srv);
        $this->assertNotEmpty($srv->status);

        for ($t = time(), $s = 10; (time() - $t) < 600 && !in_array($srv->status, array('ACTIVE', 'ERROR')); $s += 1) {
            sleep($s);
            $srv = $rs->servers->getServerDetails($srv->id);
        }
        $this->assertContains($srv->status, array('ACTIVE', 'ERROR'));

        if ($rs->servers->isExtensionSupported(ServersExtension::consoleOutput())) {
            $consoleOut = $rs->servers->getConsoleOutput($srv->id, 50);
        }

        //List Addresses test
        $addresses = $rs->servers->listAddresses($srv->id);
        $this->assertTrue(is_object($addresses));

        //Get server details test
        $srvDetails = $rs->servers->getServerDetails($srv->id);
        $this->assertInstanceOf('stdClass', $srvDetails);
        unset($srvDetails);

        //Images List test
        $imagesList = $rs->servers->images->list();
        $this->assertTrue(is_array($imagesList));
        foreach ($imagesList as $img) {
            if ($img->name == self::getTestName('image')) {
                $rs->servers->images->delete($img->id);
            }
            $imageDetails = $rs->servers->images->get($img->id);
            $this->assertTrue(is_object($imageDetails));
            unset($imageDetails);
            break;
        }
        unset($imagesList);

        //Keypairs extension test
        if ($rs->servers->isExtensionSupported(ServersExtension::keypairs())) {
            $aKeypairs = $rs->servers->keypairs->list();
            $this->assertTrue(is_array($aKeypairs));
            foreach ($aKeypairs as $v) {
                if ($v->keypair->name == self::getTestName('key')) {
                    $rs->servers->keypairs->delete($v->keypair->name);
                }
            }
            unset($aKeypairs);
            $kp = $rs->servers->keypairs->create(self::getTestName('key'));
            $this->assertNotEmpty($kp);
            $this->assertTrue(is_object($kp));

            $kptwin = $rs->servers->keypairs->get($kp->name);
            $this->assertNotEmpty($kptwin);
            $this->assertEquals($kp->public_key, $kptwin->public_key);
            unset($kptwin);

            $res = $rs->servers->keypairs->delete($kp->name);
            $this->assertTrue($res);
            unset($kp);
        }

        //Security Groups extension test
        if ($rs->servers->isExtensionSupported(ServersExtension::securityGroups())) {
            $listSecurityGroups = $rs->servers->securityGroups->list();
            $this->assertTrue(is_array($listSecurityGroups));
            foreach ($listSecurityGroups as $v) {
                if ($v->name == self::getTestName('security-group')) {
                    $rs->servers->securityGroups->delete($v->id);
                }
            }
            unset($listSecurityGroups);

            $listForSpecificServer = $rs->servers->securityGroups->list($srv->id);
            $this->assertTrue(is_array($listForSpecificServer));
            unset($listForSpecificServer);

            $sg = $rs->servers->securityGroups->create(self::getTestName('security-group'), 'This is phpunit security group test.');
            $this->assertNotEmpty($sg);
            $this->assertTrue(is_object($sg));

            $sgmirror = $rs->servers->securityGroups->get($sg->id);
            $this->assertNotEmpty($sgmirror);
            $this->assertEquals($sg->id, $sgmirror->id);
            unset($sgmirror);

            $sgrule = $rs->servers->securityGroups->addRule(array(
                "ip_protocol"     => "tcp",
                "from_port"       => "80",
                "to_port"         => "8080",
                "cidr"            => "0.0.0.0/0",
                "parent_group_id" => $sg->id,
            ));
            $this->assertNotEmpty($sgrule);
            $this->assertTrue(is_object($sgrule));
            $this->assertEquals($sg->id, $sgrule->parent_group_id);

            $ret = $rs->servers->securityGroups->deleteRule($sgrule->id);
            $this->assertTrue($ret);
            unset($sgrule);

            $ret = $rs->servers->securityGroups->delete($sg->id);
            $this->assertTrue($ret);
        }

        //Create image test
        $imageId = $rs->servers->images->create($srv->id, self::getTestName('image'));
        $this->assertTrue(is_string($imageId));

        //It requires ACTIVE state of server
//         $res = $rs->servers->resizeServer($srv->id, $srv->name, '3');
//         $this->assertTrue($res);

//         $res = $rs->servers->confirmResizedServer($srv->id);
//         $this->assertTrue($res);

        $ret = $rs->servers->images->delete($imageId);
        $this->assertTrue($ret);

        //Update server test
        $renamedDetails = $rs->servers->updateServer($srv->id, self::getTestServerName('renamed'));
        $this->assertInstanceOf('stdClass', $renamedDetails);
        $this->assertEquals(self::getTestServerName('renamed'), $renamedDetails->server->name);
        unset($renamedDetails);

        //Delete Server test
        $ret = $rs->servers->deleteServer($srv->id);
        $this->assertTrue($ret);
    }
}