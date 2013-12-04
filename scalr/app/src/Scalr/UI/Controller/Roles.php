<?php

class Scalr_UI_Controller_Roles extends Scalr_UI_Controller
{
    const CALL_PARAM_NAME = 'roleId';

    public static function getPermissionDefinitions()
    {
        return array(
            'edit' => 'Edit',
            'xSaveRole' => 'Edit'
        );
    }

    public function hasAccess()
    {
        return true;
    }

    public function xGetList2Action()
    {
        $total = 0;
        $roles = array();
        $filterCatId = $this->getParam('catId');
        $filterOsFamily = $this->getParam('osFamily');
        $filterKeyword = $this->getParam('keyword');

        $filterPlatform = $this->getParam('platform');

        $e_platforms = $this->getEnvironment()->getEnabledPlatforms();
        $platforms = array();
        $l_platforms = SERVER_PLATFORMS::GetList();
        foreach ($e_platforms as $platform)
            $platforms[$platform] = $l_platforms[$platform];

        if ($filterPlatform)
            if (!$platforms[$filterPlatform])
                throw new Exception("Selected cloud not enabled in current environment");

        if ($filterCatId === 'shared') {
            $args[] = 0;
            if (!$filterPlatform)
                $roles_sql = "SELECT id FROM roles WHERE generation = '2' AND env_id=? AND id IN (SELECT role_id FROM role_images WHERE platform IN ('".implode("','", array_keys($platforms))."'))";
            else {
                $roles_sql = "SELECT id FROM roles WHERE generation = '2' AND env_id=? AND id IN (SELECT role_id FROM role_images WHERE platform = ?)";
                $args[] = $filterPlatform;
            }

            if ($filterOsFamily) {
                $roles_sql .= ' AND os_family = ?';
                $args[] = $filterOsFamily;
            }

            $dbRoles = $this->db->Execute($roles_sql, $args);
            $software = array();
            /*
            $software = array(
                'mysql' => array(),
                'postgresql' => array(),
                'apache' => array(),
                'nginx' => array(),
                'redis' => array(),
                'mongodb' => array(),
                'base' => array(),
                'percona' => array(),
                'memcached' => array(),
                'rabbitmq' => array(),
                'lamp' => array()
            );

            software[type] = array(
                filters = array(
                    'arch' = 32 / 64 (default: 64)
                    'hvm' => 1 / 0 (default: 0)
                    'type' => ebs / instance-store (default: ebs)
                ),
                roles = array()
            )
            */
            $software = array();
            $softwareOrdering = array(
                'base' => 0,
                'mysql' => 10,
                'percona' => 20,
                'mariadb' => 10,
                'postgresql' => 30,
                'mongodb' => 40,
                'redis' => 50,
                'apache' => 60,
                'lamp' => 70,
                'tomcat' => 80,
                'haproxy' => 90,
                'nginx' => 100,
                'memcached' => 110,
                'rabbitmq' => 120,
                'vpcrouter' => 130
            );
            foreach ($dbRoles as $role) {
                $dbRole = DBRole::loadById($role['id']);

                // Get type
                if ($dbRole->hasBehavior(ROLE_BEHAVIORS::VPC_ROUTER))
                    $type = 'vpcrouter';
                elseif ($dbRole->hasBehavior(ROLE_BEHAVIORS::APACHE) && ($dbRole->hasBehavior(ROLE_BEHAVIORS::MYSQL2) || $dbRole->hasBehavior(ROLE_BEHAVIORS::MYSQL) || $dbRole->hasBehavior(ROLE_BEHAVIORS::PERCONA) || $dbRole->hasBehavior(ROLE_BEHAVIORS::MARIADB)))
                    $type = 'lamp';
                elseif ($dbRole->hasBehavior(ROLE_BEHAVIORS::MYSQL2))
                    $type = 'mysql';
                elseif ($dbRole->hasBehavior(ROLE_BEHAVIORS::APACHE))
                    $type = 'apache';
                elseif ($dbRole->hasBehavior(ROLE_BEHAVIORS::TOMCAT))
                    $type = ROLE_BEHAVIORS::TOMCAT;
                elseif ($dbRole->hasBehavior(ROLE_BEHAVIORS::NGINX))
                    $type = 'nginx';
                elseif ($dbRole->hasBehavior(ROLE_BEHAVIORS::HAPROXY))
                    $type = 'haproxy';
                elseif ($dbRole->getDbMsrBehavior())
                    $type = $dbRole->getDbMsrBehavior();
                elseif ($dbRole->hasBehavior(ROLE_BEHAVIORS::MONGODB))
                    $type = ROLE_BEHAVIORS::MONGODB;
                elseif ($dbRole->hasBehavior(ROLE_BEHAVIORS::RABBITMQ))
                    $type = ROLE_BEHAVIORS::RABBITMQ;
                elseif ($dbRole->hasBehavior(ROLE_BEHAVIORS::MEMCACHED))
                    $type = ROLE_BEHAVIORS::MEMCACHED;
                elseif ($dbRole->hasBehavior(ROLE_BEHAVIORS::BASE))
                    $type = ROLE_BEHAVIORS::BASE;
                elseif ($dbRole->hasBehavior(ROLE_BEHAVIORS::MYSQL))
                    continue;

                // Set hvm flag
                $hvm = stristr($dbRole->name, '-hvm-') ? 1 : 0;

                // Set arch flag
                $architecture = (stristr($dbRole->name, '64-')) ? 'x86_64' : 'i386';

                $images = $dbRole->getImages(true);
                foreach ($images as $cloud => $locations) {
                    if (!$platforms[$cloud]) {
                        unset($images[$cloud]);
                    } else {
                        foreach ($locations as $location => $image) {
                            if (!$image['architecture']) {
                                $images[$cloud][$location]['architecture'] = $architecture;
                            }
                        }
                    }
                }

                $item = array(
                    'role_id'       => $dbRole->id,
                    'name'		    => $dbRole->name,
                    'behaviors'     => $dbRole->getBehaviors(),
                    'origin'        => $dbRole->origin,
                    'cat_name'	    => $dbRole->getCategoryName(),
                    'cat_id'	    => $dbRole->catId,
                    'os_name'       => $dbRole->os,
                    'os_family'     => $dbRole->osFamily,
                    'os_generation' => $dbRole->osGeneration,
                    'os_version'    => $dbRole->osVersion,
                    'images'        => $images,
                    'hvm'           => $hvm,
                    'ebs'           => 1,
                    'description'   => $dbRole->description
                );

                $software[$type]['roles'][] = $item;

                $software[$type]['name'] = $type;
                $software[$type]['ordering'] = isset($softwareOrdering[$type]) ? $softwareOrdering[$type] : 1000;
                $total++;
            }
            $software = array_values($software);
        } else {
            $args[] = $this->getEnvironmentId();
            $roles_sql = "
                SELECT r.id, r.env_id
                FROM roles r
                LEFT JOIN roles_queue q ON r.id = q.role_id
                WHERE generation = '2' AND env_id IN(0, ?)
                AND q.id IS NULL
                AND r.id IN (
                    SELECT role_id
                    FROM role_images
                    WHERE platform
            ";
            if (!$filterPlatform)
                $roles_sql .= " IN ('".implode("','", array_keys($platforms))."')";
            else {
                $roles_sql .= " = ?";
                $args[] = $filterPlatform;
            }
            $roles_sql .= ')';

            if ($filterCatId === 'search'){
                $roles_sql .= ' AND r.name LIKE ' . $this->db->qstr('%' . trim($filterKeyword) . '%');
            } elseif ($filterCatId === 'recent') {
            } elseif ($filterCatId) {
                $roles_sql .= ' AND r.cat_id = ?';
                $args[] = $filterCatId;
            }

            if ($filterOsFamily) {
                $roles_sql .= ' AND r.os_family = ?';
                $args[] = $filterOsFamily;
            }

            $roles_sql .= ' GROUP BY r.id';

            if ($filterCatId === 'recent') {
                $roles_sql .= ' ORDER BY r.id DESC LIMIT 10';
            }

            $dbRoles = $this->db->Execute($roles_sql, $args);

            $globalVars = new Scalr_Scripting_GlobalVariables($this->getEnvironmentId(), Scalr_Scripting_GlobalVariables::SCOPE_ROLE);

            foreach ($dbRoles as $role) {
                $dbRole = DBRole::loadById($role['id']);

                $architecture = (stristr($dbRole->name, '64-')) ? 'x86_64' : 'i386';
                $images = $dbRole->getImages(true);
                foreach ($images as $cloud => $locations) {
                    if (!$platforms[$cloud]) {
                        unset($images[$cloud]);
                    } else if ($dbRole->envId == 0) {
                        foreach ($locations as $location => $image) {
                            if (!$image['architecture']) {
                                $images[$cloud][$location]['architecture'] = $architecture;
                            }
                        }
                    }
                }

                $roles[] = array(
                    'role_id'       => $dbRole->id,
                    'name'		    => $dbRole->name,
                    'behaviors'     => $dbRole->getBehaviors(),
                    'origin'        => $dbRole->origin,
                    'cat_name'	    => $dbRole->getCategoryName(),
                    'cat_id'	    => $dbRole->catId,
                    'os_name'       => $dbRole->os,
                    'os_family'     => $dbRole->osFamily,
                    'os_generation' => $dbRole->osGeneration,
                    'os_version'    => $dbRole->osVersion,
                    'images'        => $images,
                    'tags'			=> $dbRole->getTags(),
                    'variables'     => json_encode($globalVars->getValues($dbRole->id, 0, 0)),
                    'shared'        => $role['env_id'] == 0,
                    'description'   => $dbRole->description,
                );
            }
            $total = count($roles);
        }

        $moduleParams = array(
            'roles' => $roles,
            'software' => $software,
            'total' => $total
            /*'platforms' => $platforms,
            'categories' => $this->db->GetAll("SELECT id, name FROM role_categories WHERE env_id = 0 OR env_id = ?",
                array($this->environment->id)
            )*/
        );
        $this->response->data($moduleParams);
    }


    public function xGetListAction()
    {
        $roles = $this->getList();
        $platforms = self::loadController('Platforms')->getEnabledPlatforms(true);
        $dbGroups = $this->db->GetAll("SELECT id, name FROM role_categories WHERE env_id = 0 OR env_id = ?",
            array($this->environment->id)
        );
        $groups = array();
        foreach ($dbGroups as $g)
            $groups[$g['id']] = $g['name'];

        $moduleParams = array(
            'roles' => $roles,
            'platforms' => $platforms,
            'groups' => $groups
        );

        $this->response->data($moduleParams);
    }

    public function getList($isBeta = false)
    {
        $roles = array();

        $e_platforms = $this->getEnvironment()->getEnabledPlatforms();
        $platforms = array();
        $l_platforms = SERVER_PLATFORMS::GetList();
        foreach ($e_platforms as $platform)
            $platforms[$platform] = $l_platforms[$platform];

        $roles_sql = "SELECT id FROM roles WHERE (env_id = 0 OR env_id=?) AND id IN (SELECT role_id FROM role_images WHERE platform IN ('".implode("','", array_keys($platforms))."'))";
        $args[] = $this->getEnvironmentId();

        $dbroles = $this->db->Execute($roles_sql, $args);
        while ($role = $dbroles->FetchRow()) {
            if ($this->db->GetOne("SELECT id FROM roles_queue WHERE role_id=?", array($role['id'])))
                continue;

            $dbRole = DBRole::loadById($role['id']);

            // Skip ami-script roles
            if ($dbRole->generation != 2 && $dbRole->origin == ROLE_TYPE::SHARED)
                continue;

            // Skip old generation of mysql roles
            if ($dbRole->hasBehavior(ROLE_BEHAVIORS::MYSQL) && $dbRole->origin == ROLE_TYPE::SHARED)
                continue;

            $role_platforms = $dbRole->getPlatforms();
            $enabled_role_platforms = array();
            $role_locations = array();
            foreach ($role_platforms as $platform) {
                   if ($this->environment->isPlatformEnabled($platform)) {
                    $role_locations[$platform] = $dbRole->getCloudLocations($platform);
                    $enabled_role_platforms[] = $platform;
                   }
            }

            $roles[] = array(
                'role_id'				=> $dbRole->id,
                'group'					=> $dbRole->getCategoryName(),
                'cat_id'                => $dbRole->catId,
                'name'					=> $dbRole->name,
                'generation'			=> $dbRole->generation,
                'behaviors'				=> implode(",", $dbRole->getBehaviors()),
                'origin'				=> $dbRole->origin,
                'platforms'				=> implode(",", $enabled_role_platforms),
                'locations'				=> $role_locations,
                'os'					=> $dbRole->os,
                'os_family'             => $dbRole->osFamily,
                'os_version'            => $dbRole->osVersion,
                'os_generation'         => $dbRole->osGeneration,
                'tags'					=> $dbRole->getTags(),
                'images'				=> $dbRole->getImages(true)
            );
        }

        return $roles;
    }

    public function xGetMigrateDetailsAction()
    {
        $role = DBRole::loadById($this->getParam('roleId'));
        if ($role->envId != 0)
            $this->user->getPermissions()->validate($role);
        else
            throw new Exception("You cannot migrate shared role");

        $images = $role->getImages();

        if (!$this->request->getEnvironment()->isPlatformEnabled(SERVER_PLATFORMS::EC2))
            throw new Exception('You can migrate image between regions only on EC2 cloud');

        if (!$images[SERVER_PLATFORMS::EC2])
            throw new Exception('You can migrate image between regions only on EC2 cloud');

        $platform = PlatformFactory::NewPlatform(SERVER_PLATFORMS::EC2);
        $locationsList = $platform->getLocations();

        $availableSources = array_keys($images[SERVER_PLATFORMS::EC2]);
        $availableSourcesL = array();
        foreach ($availableSources as $sourceLocation)
            $availableSourcesL[] = array('cloudLocation' => $sourceLocation, 'name' => $locationsList[$sourceLocation]);

        foreach ($locationsList as $location => $name) {
            if (!in_array($location, $availableSources))
                $availableDestinations[] = array('cloudLocation' => $location, 'name' => $name);
        }

        $this->response->data(array(
            'availableSources' => $availableSourcesL,
            'availableDestinations' => $availableDestinations,
            'roleName' => $role->name,
            'roleId' => $role->id
        ));
    }

    public function xMigrateAction()
    {
        $role = DBRole::loadById($this->getParam('roleId'));
        if ($role->envId != 0)
            $this->user->getPermissions()->validate($role);
        else
            throw new Exception("You cannot migrate shared role");

        $images = $role->getImages(true);

        $aws = $this->request->getEnvironment()->aws($this->getParam('sourceRegion'));
        $newImageId = $aws->ec2->image->copy(
            $this->getParam('sourceRegion'),
            $images[SERVER_PLATFORMS::EC2][$this->getParam('sourceRegion')]['image_id'],
            $role->name,
            "Scalr role: {$role->name}",
            null,
            $this->getParam('destinationRegion')
        );

        $role->setImage(
            $newImageId,
            SERVER_PLATFORMS::EC2,
            $this->getParam('destinationRegion'),
            $images[SERVER_PLATFORMS::EC2][$this->getParam('sourceRegion')]['szr_version'],
            $images[SERVER_PLATFORMS::EC2][$this->getParam('sourceRegion')]['architecture']
        );

        $this->response->success('Role successfully migrated');
    }

    public function xCloneAction()
    {
        $dbRole = DBRole::loadById($this->getParam('roleId'));
        if ($dbRole->envId != 0)
            $this->user->getPermissions()->validate($dbRole);

        $chkRoleId = $this->db->GetOne("SELECT id FROM roles WHERE name=? AND (env_id = '0' OR env_id = ?)",
            array($this->getParam('newRoleName'), $this->getEnvironmentId())
        );

        if ($chkRoleId) {
            if (!$this->db->GetOne("SELECT id FROM roles_queue WHERE role_id=?", array($chkRoleId)))
                throw new Exception('Selected role name is already used. Please select another one.');
        }

        $dbRole->cloneRole($this->getParam('newRoleName'), $this->user->getAccountId(), $this->environment->id);

        $this->response->success('Role successfully cloned');
    }

    public function xRemoveAction()
    {
        $this->request->defineParams(array(
            'roles' => array('type' => 'json'),
            'removeFromCloud'
        ));

        foreach ($this->getParam('roles') as $id) {
            $dbRole = DBRole::loadById($id);

            if ($this->user->getType() != Scalr_Account_User::TYPE_SCALR_ADMIN)
                $this->user->getPermissions()->validate($dbRole);

            if ($this->db->GetOne("SELECT COUNT(*) FROM farm_roles WHERE role_id=? AND farmid IN (SELECT id FROM farms WHERE clientid=?)", array($dbRole->id, $this->user->getAccountId())) == 0) {

                if ($this->getParam('removeFromCloud')) {
                    $images = $dbRole->getImages();
                    $doNotDeleteImage = false;

                    foreach ($images as $platform => $cloudLocations) {
                        foreach ($cloudLocations as $cloudLocation => $imageId) {
                            $usedBy = $this->db->GetOne("SELECT COUNT(*) FROM role_images WHERE image_id = ?", array($imageId));
                            if ($usedBy > 1)
                                $doNotDeleteImage = true;
                        }
                    }

                    if (!$doNotDeleteImage)
                       $this->db->Execute("INSERT INTO roles_queue SET `role_id`=?, `action`=?, dtadded=NOW()", array($dbRole->id, 'remove'));
                    else
                        $dbRole->remove();

                } else {
                    $dbRole->remove();
                }
            }
            else
                throw new Exception(sprintf(_("Role '%s' used by your farms and cannot be removed."), $dbRole->name));
        }

        $this->response->success('Selected roles successfully removed');
    }

    public function builderAction()
    {
        $platforms = array();
        foreach (self::loadController('Platforms')->getEnabledPlatforms(true) as $k => $v) {
            if (in_array($k, array(SERVER_PLATFORMS::EC2, SERVER_PLATFORMS::GCE, SERVER_PLATFORMS::RACKSPACENG_US, SERVER_PLATFORMS::RACKSPACENG_UK)))
                $platforms[$k] = $v;
        }

        $images = json_decode(file_get_contents(APPPATH . '/www/storage/images.json'), true);
        foreach ($platforms as $k => $v) {
            $p = PlatformFactory::NewPlatform($k);
            $platforms[$k]['images'] = $images[$k];
        }

        $this->response->page('ui/roles/builder.js', array(
            'platforms' => $platforms,
            'environment' => '#/account/environments/view?envId=' . $this->getEnvironmentId()
        ), array(), array('ui/roles/builder.css'));
    }

    public function xBuildAction()
    {
        $this->request->defineParams(array(
            'platform'      => array('type' => 'string'),
            'architecture'  => array('type' => 'string'),
            'behaviors'     => array('type' => 'json'),
            'roleName'      => array('type' => 'string'),
            'imageId'       => array('type' => 'string'),
            'location'      => array('type' => 'string'),
            'advanced' => array('type' => 'json'),
            'chef' => array('type' => 'json')
        ));

        if (strlen($this->getParam('roleName')) < 3)
            throw new Exception(_("Role name should be greater than 3 chars"));

        if (! preg_match("/^[A-Za-z0-9-]+$/si", $this->getParam('roleName')))
            throw new Exception(_("Role name is incorrect"));

        $chkRoleId = $this->db->GetOne("SELECT id FROM roles WHERE name=? AND (env_id = '0' OR env_id = ?)",
            array($this->getParam('roleName'), $this->getEnvironmentId())
        );

        if ($chkRoleId) {
            if (!$this->db->GetOne("SELECT id FROM roles_queue WHERE role_id=?", array($chkRoleId)))
                throw new Exception('Selected role name is already used. Please select another one.');
        }

        $imageId = $this->getParam('imageId');

        $advanced = $this->getParam('advanced');
        $chef = $this->getParam('chef');

        $behaviours = implode(",", array_values($this->getParam('behaviors')));

        // Create server
        $creInfo = new ServerCreateInfo($this->getParam('platform'), null, 0, 0);
        $creInfo->clientId = $this->user->getAccountId();
        $creInfo->envId = $this->getEnvironmentId();
        $creInfo->farmId = 0;
        $creInfo->SetProperties(array(
            SERVER_PROPERTIES::SZR_IMPORTING_BEHAVIOR => $behaviours,
            SERVER_PROPERTIES::SZR_KEY => Scalr::GenerateRandomKey(40),
            SERVER_PROPERTIES::SZR_KEY_TYPE => SZR_KEY_TYPE::PERMANENT,
            SERVER_PROPERTIES::SZR_VESION => "0.13.0",
            SERVER_PROPERTIES::SZR_IMPORTING_MYSQL_SERVER_TYPE => "mysql",
            SERVER_PROPERTIES::SZR_DEV_SCALARIZR_BRANCH => $advanced['scalrbranch'],
            SERVER_PROPERTIES::ARCHITECTURE => $this->getParam('architecture'),
            SERVER_PROPERTIES::SZR_IMPORTING_LEAVE_ON_FAIL => $advanced['dontterminatefailed'] == 'on' ? 1 : 0,

            SERVER_PROPERTIES::SZR_IMPORTING_CHEF_SERVER_ID => $chef['chef.server'],
            SERVER_PROPERTIES::SZR_IMPORTING_CHEF_ENVIRONMENT => $chef['chef.environment'],
            SERVER_PROPERTIES::SZR_IMPORTING_CHEF_ROLE_NAME => $chef['chef.role']
        ));

        $dbServer = DBServer::Create($creInfo, true);
        $dbServer->status = SERVER_STATUS::TEMPORARY;
        $dbServer->save();

        //Launch server
        $launchOptions = new Scalr_Server_LaunchOptions();
        $launchOptions->imageId = $imageId;
        $launchOptions->cloudLocation = $this->getParam('cloud_location');
        $launchOptions->architecture = $this->getParam('architecture');

        $platform = PlatformFactory::NewPlatform($this->getParam('platform'));

        switch($this->getParam('platform')) {
            case SERVER_PLATFORMS::RACKSPACE:
                if ($this->getParam('osfamily') == 'ubuntu')
                    $launchOptions->serverType = 1;
                else
                    $launchOptions->serverType = 3;
                break;
            case SERVER_PLATFORMS::RACKSPACENG_US:
                if ($this->getParam('osfamily') == 'ubuntu')
                    $launchOptions->serverType = 3;
                else
                    $launchOptions->serverType = 3;
                break;
            case SERVER_PLATFORMS::RACKSPACENG_UK:
                if ($this->getParam('osfamily') == 'ubuntu')
                    $launchOptions->serverType = 3;
                else
                    $launchOptions->serverType = 3;
                break;
            case SERVER_PLATFORMS::EC2:
                if ($this->getParam('osfamily') == 'oel') {
                    $launchOptions->serverType = 'm1.large';
                    $bundleType = SERVER_SNAPSHOT_CREATION_TYPE::EC2_EBS_HVM;
                }
                elseif ($this->getParam('osfamily') == 'rhel') {
                    $launchOptions->serverType = 'm1.large';
                }
                else
                    $launchOptions->serverType = 'm1.small';

                $launchOptions->userData = "#cloud-config\ndisable_root: false";
                break;
            case SERVER_PLATFORMS::GCE:
                $launchOptions->serverType = 'n1-standard-1';
                $locations = array_keys($platform->getLocations());

                $launchOptions->cloudLocation = $locations[0];

                $bundleType = SERVER_SNAPSHOT_CREATION_TYPE::GCE_STORAGE;
                break;
        }

        if ($advanced['servertype'])
            $launchOptions->serverType = $advanced['servertype'];

        if ($advanced['availzone'])
            $launchOptions->availZone = $advanced['availzone'];

        if ($advanced['region'])
            $launchOptions->cloudLocation = $advanced['region'];

        //Add Bundle task
        $creInfo = new ServerSnapshotCreateInfo(
            $dbServer,
            $this->getParam('roleName'),
            SERVER_REPLACEMENT_TYPE::NO_REPLACE
        );

        $bundleTask = BundleTask::Create($creInfo, true);

        if ($bundleType)
            $bundleTask->bundleType = $bundleType;

        $bundleTask->cloudLocation = $launchOptions->cloudLocation;
        $bundleTask->save();

        $bundleTask->Log(sprintf("Launching temporary server (%s)", serialize($launchOptions)));

        $dbServer->SetProperty(SERVER_PROPERTIES::SZR_IMPORTING_BUNDLE_TASK_ID, $bundleTask->id);

        try {
            $platform->LaunchServer($dbServer, $launchOptions);
            $bundleTask->Log(_("Temporary server launched. Waiting for running state..."));
        }
        catch(Exception $e) {
            $bundleTask->SnapshotCreationFailed(sprintf(_("Unable to launch temporary server: %s"), $e->getMessage()));
        }

        $this->response->data(array('bundleTaskId' => $bundleTask->id));
    }

    /**
    * View roles listView with filters
    */
    public function viewAction()
    {
        $this->response->page('ui/roles/view.js', array(
            'locations' => self::loadController('Platforms')->getCloudLocations('all'),
            'isScalrAdmin' => ($this->user->getType() == Scalr_Account_User::TYPE_SCALR_ADMIN)
        ));
    }

    public function createAction()
    {
        $this->editAction();
    }

    /**
    * View edit role page
    */
    public function editAction()
    {
        // declare types of input variables (available types: int, string (default), bool, json, array; may be include default value for variable)
        $this->request->defineParams(array(
            'roleId' => array('type' => 'int')
        ));

        $params = array('platforms' => array(), 'isScalrAdmin' => ($this->user->getType() == Scalr_Account_User::TYPE_SCALR_ADMIN));

        if (! $params['isScalrAdmin'])
            $ePlatforms = $this->getEnvironment()->getEnabledPlatforms();
        else
            $ePlatforms = array_keys(SERVER_PLATFORMS::GetList());

        $lPlatforms = SERVER_PLATFORMS::GetList();

        try {
            $llist = array();
            foreach ($ePlatforms as $platform) {
                $locations = array();
                foreach (PlatformFactory::NewPlatform($platform)->getLocations() as $key => $loc) {
                    $locations[] = array('id' => $key, 'name' => $loc);
                    $llist[$key] = $loc;
                }

                $params['platforms'][] = array(
                    'id' => $platform,
                    'name' => $lPlatforms[$platform],
                    'locations' => $locations
                );
            }
        } catch (Exception $e) {}

        $params['scriptData'] = self::loadController('Scripts')->getScriptingData();

        if ($this->getParam('roleId')) {
            $dbRole = DBRole::loadById($this->getParam('roleId'));

            if ($this->user->getType() != Scalr_Account_User::TYPE_SCALR_ADMIN)
                $this->user->getPermissions()->validate($dbRole);

            $images = array();
            foreach ($dbRole->getImages(true) as $platform => $locations) {
                foreach ($locations as $location => $imageInfo)
                    $images[] = array(
                        'image_id' 		=> $imageInfo['image_id'],
                        'platform' 		=> $platform,
                        'location' 		=> $location,
                        'platform_name' => SERVER_PLATFORMS::GetName($platform),
                        'location_name'	=> $llist[$location],
                        'architecture'	=> $imageInfo['architecture']
                    );
            }

            $params['tags'] = array_flip($dbRole->getTags());

            $params['role'] = array(
                'id'			=> $dbRole->id,
                'name'			=> $dbRole->name,
                'arch'          => $imageInfo['architecture'],
                'os'			=> $dbRole->os,
                'agent'			=> $dbRole->generation,
                'description'	=> $dbRole->description,
                'behaviors'		=> $dbRole->getBehaviors(),
                'properties'	=> array(DBRole::PROPERTY_SSH_PORT => $dbRole->getProperty(DBRole::PROPERTY_SSH_PORT)),
                'images'		=> $images,
                'parameters'	=> $dbRole->getParameters(),
                'scripts'       => $dbRole->getScripts(),
                'security_rules' => $dbRole->getSecurityRules()
            );

            if ($dbRole->origin == ROLE_TYPE::CUSTOM) {
                $variables = new Scalr_Scripting_GlobalVariables($this->getEnvironmentId(), Scalr_Scripting_GlobalVariables::SCOPE_ROLE);
                $params['role']['variables'] = json_encode($variables->getValues($dbRole->id));
            }

            if ($params['role']['security_rules']) {
                foreach ($params['role']['security_rules'] as &$r) {
                    $chunks = explode(":", $r['rule']);
                    $r['ipProtocol'] = $chunks[0];
                    $r['fromPort'] = $chunks[1];
                    $r['toPort'] = $chunks[2];
                    $r['cidrIp'] = $chunks[3];
                    $r['comment'] = $this->db->GetOne("SELECT `comment` FROM `comments` WHERE `env_id` = ? AND `rule` = ? AND `sg_name` = ?", array(
                        $dbRole->envId, $r['rule'], "role:{$params['role']['id']}"
                    ));
                    if (!$r['comment'])
                        $r['comment'] = "";
                }
            }

            if (!$params['role']['properties'][DBRole::PROPERTY_SSH_PORT])
                $params['role']['properties'][DBRole::PROPERTY_SSH_PORT] = 22;

            $this->response->page('ui/roles/edit.js', $params, array('ui/scripts/scriptfield.js', 'ui/core/variablefield.js'), array('ui/scripts/scriptfield.css', 'ui/core/variablefield.css'));
        } else {
            $params['tags'] = array();
            $params['role'] = array(
                'id'			=> 0,
                'name'			=> "",
                'arch'			=> "x86_64",
                'agent'			=> 2,
                'description'	=> "",
                'behaviors'		=> array(),
                'properties'	=> array(DBRole::PROPERTY_SSH_PORT => 22),
                'images'		=> array(),
                'parameters'	=> array(),
                'scripts'       => array()
            );

            $this->response->page('ui/roles/edit.js', $params,  array('ui/scripts/scriptfield.js'), array('ui/scripts/scriptfield.css'));
        }
    }

    public function xGetRoleParamsAction()
    {
        $this->request->defineParams(array(
            'roleId' => array('type' => 'int'),
            'farmId' => array('type' => 'int'),
            'cloudLocation'
        ));

        try {
            $dbRole = DBRole::loadById($this->getParam('roleId'));
            if ($dbRole->envId != 0)
                $this->user->getPermissions()->validate($dbRole);
        }
        catch (Exception $e) {
            $this->response->data(array('params' => array()));
            return;
        }

        $params = $this->db->GetAll("SELECT * FROM role_parameters WHERE role_id=? AND hash NOT IN('apache_http_vhost_template','apache_https_vhost_template')",
            array($dbRole->id)
        );

        foreach ($params as $key => $param) {
            $value = false;

            try {
                if($this->getParam('farmId')) {
                    $dbFarmRole = DBFarmRole::Load($this->getParam('farmId'), $this->getParam('roleId'), $this->getParam('cloudLocation'));

                    $value = $this->db->GetOne("SELECT value FROM farm_role_options WHERE farm_roleid=? AND hash=?",
                        array($dbFarmRole->ID, $param['hash'])
                    );
                }
            }
            catch(Exception $e) {}

            // Get field value
            if ($value === false || $value === null)
                $value = $param['defval'];

            $params[$key]['value'] = str_replace("\r", "", $value);
        }

        $this->response->data(array('params' => $params));
    }

    /**
    * Save role informatiom
    */
    public function xSaveRoleAction()
    {
        $this->request->defineParams(array(
            'roleId' => array('type' => 'int'),
            'agent' => array('type' => 'int'),
            'behaviors' => array('type' => 'array'),
            'tags' => array('type' => 'array'),
            'arch', 'description', 'name', 'os',
            'parameters' => array('type' => 'json'),
            'remove_images' => array('type' => 'json'),
            'images' => array('type' => 'json'),
            'properties' => array('type' => 'json'),
            'scripts' => array('type' => 'json')
        ));

        $id = $this->getParam('roleId');
        $parameters = $this->getParam('parameters');

        if ($id == 0) {
            if ($this->user->getType() != Scalr_Account_User::TYPE_SCALR_ADMIN) {
                $origin = ROLE_TYPE::CUSTOM;
                $envId = $this->environment->id;
                $clientId = $this->user->getAccountId();
            } else {
                $origin = ROLE_TYPE::SHARED;
                $envId = 0;
                $clientId = 0;
            }

            $dbRole = new DBRole(0);

            $dbRole->generation = ($this->getParam('agent') == 'scalarizr' || $this->getParam('agent') == 2) ? 2 : 1; // ($post_agent != 'scalarizr') ? 1 : 2;
            $dbRole->origin = $origin;
            $dbRole->envId = $envId;
            $dbRole->clientId = $clientId;
            $dbRole->name = $this->getParam('name');
            $dbRole->os = $this->getParam('os');

            $rules = array(
                array('rule' => 'icmp:-1:-1:0.0.0.0/0'),
                array('rule' => 'tcp:22:22:0.0.0.0/0')
            );

            foreach ($this->getParam('behaviors') as $behavior) {
                if ($behavior == ROLE_BEHAVIORS::NGINX || $behavior == ROLE_BEHAVIORS::APACHE) {
                    if (empty($parameters)) {
                        $param = new stdClass();
                        $param->name = 'Nginx HTTPS Vhost Template';
                        $param->required = '1';
                        $param->defval = @file_get_contents(dirname(__FILE__)."/../../../../templates/services/nginx/ssl.vhost.tpl");
                        $param->type = 'textarea';
                        $parameters[] = $param;
                    }
                }

                if ($behavior == ROLE_BEHAVIORS::MYSQL) {
                    $rules[] = array('rule' => "tcp:3306:3306:0.0.0.0/0");
                }

                if ($behavior == ROLE_BEHAVIORS::CASSANDRA) {
                    $rules[] = array('rule' => "tcp:9160:9160:0.0.0.0/0");
                }

                if ($behavior == ROLE_BEHAVIORS::CF_DEA) {
                    $rules[] = array('rule' => "tcp:12345:12345:0.0.0.0/0");
                }

                if ($behavior == ROLE_BEHAVIORS::CF_ROUTER) {
                    $rules[] = array('rule' => "tcp:2222:2222:0.0.0.0/0");
                }

                foreach (Scalr_Role_Behavior::loadByName($behavior)->getSecurityRules() as $rr)
                    $rules[] = array('rule' => $rr);
            }

            $dbRole = $dbRole->save();

            $soft = explode("\n", trim($this->getParam('software')));
            $software = array();
            if (count($soft) > 0) {
                foreach ($soft as $softItem) {
                    $itm = explode("=", $softItem);
                    $software[trim($itm[0])] = trim($itm[1]);
                }

                $dbRole->setSoftware($software);
            }

            $dbRole->setBehaviors(array_values($this->getParam('behaviors')));
        } else {
            $dbRole = DBRole::loadById($id);

            if ($this->user->getType() != Scalr_Account_User::TYPE_SCALR_ADMIN)
                $this->user->getPermissions()->validate($dbRole);

            if ($dbRole->origin == ROLE_TYPE::CUSTOM) {
                $variables = new Scalr_Scripting_GlobalVariables($this->getEnvironmentId(), Scalr_Scripting_GlobalVariables::SCOPE_ROLE);
                $variables->setValues(json_decode($this->getParam('variables'), true), $dbRole->id);
            }

            $rules = json_decode($this->getParam('security_rules'), true);
            foreach ($rules as &$r) {
                $r['rule'] = "{$r['ipProtocol']}:{$r['fromPort']}:{$r['toPort']}:{$r['cidrIp']}";
            }
        }

        $dbRole->description = $this->getParam('description');

        $this->db->Execute("DELETE FROM role_security_rules WHERE role_id = ?", array($dbRole->id));
        foreach ($rules as $rule) {
            $this->db->Execute("INSERT INTO role_security_rules SET `role_id`=?, `rule`=?", array(
                    $dbRole->id, $rule['rule']
            ));
            if ($rule['comment']) {
                $this->db->Execute("REPLACE INTO `comments` SET `env_id` = ?, `comment` = ?, `sg_name` = ?, `rule` = ?", array(
                        $this->getEnvironment() ? $this->getEnvironmentId() : 0, $rule['comment'], "role:{$dbRole->id}", $rule['rule']
                ));
            }
        }

        foreach ($this->getParam('remove_images') as $imageId)
            $dbRole->removeImage($imageId);

        foreach ($this->getParam('images') as $image) {
            $image = (array)$image;
            $dbRole->setImage(
                $image['image_id'],
                $image['platform'],
                $image['location'],
                $image['szr_version'],
                $image['architecture']
            );
        }

        foreach ($this->getParam('properties') as $k => $v)
            $dbRole->setProperty($k, $v);

        $dbRole->setParameters($parameters);
        $dbRole->setScripts($this->getParam('scripts'));


        if ($this->user->getType() == Scalr_Account_User::TYPE_SCALR_ADMIN)
            $dbRole->setTags($this->getParam('tags'));

        $dbRole->save();

        $this->response->success('Role saved');
    }

    /**
    * Get list of roles for listView
    */
    public function xListRolesAction()
    {
        $this->request->defineParams(array(
            'client_id' => array('type' => 'int'),
            'roleId' => array('type' => 'int'),
            'cloudLocation', 'origin', 'query',
            'sort' => array('type' => 'json')
        ));

        if ($this->user->getType() == Scalr_Account_User::TYPE_SCALR_ADMIN) {
            $sql = 'SELECT id from roles WHERE env_id = 0 AND :FILTER:';
            $args = array();
        } else {
            $sql = 'SELECT id from roles WHERE env_id IN (?, 0) AND generation=? AND :FILTER:';
            $args = array($this->getEnvironmentId(), 2);
        }

        if ($this->getParam('cloudLocation')) {
            $sql .= ' AND id IN (SELECT role_id FROM role_images WHERE cloud_location = ?)';
            $args[] = $this->getParam('cloudLocation');
        }

        if ($this->getParam('roleId')) {
            $sql .= ' AND id = ?';
            $args[] = $this->getParam('roleId');
        }

        if ($this->getParam('origin')) {
            $sql .= ' AND origin = ?';
            $args[] = $this->getParam('origin');
        }

        if ($this->getParam('status')) {
            $sql .= ' AND (';
            $used = $this->getParam('status') == 'Used' ? true : false;
            if ($this->user->getAccountId() != 0) {
                $sql .= 'id ' . ($used ? '' : 'NOT') . ' IN(SELECT role_id FROM farm_roles WHERE farmid IN(SELECT id from farms WHERE env_id = ?))';
                $sql .= ' OR id ' . ($used ? '' : 'NOT') . ' IN(SELECT new_role_id FROM farm_roles WHERE farmid IN(SELECT id from farms WHERE env_id = ?))';
                $args[] = $this->getEnvironmentId();
                $args[] = $this->getEnvironmentId();
            } else {
                $sql .= 'id ' . $used ? '' : 'NOT' . ' IN(SELECT role_id FROM farm_roles)';
                $sql .= ' OR ' . $used ? '' : 'NOT' . ' id IN(SELECT new_role_id FROM farm_roles)';
            }
            $sql .= ')';
        }

        $response = $this->buildResponseFromSql2($sql, array('name', 'os'), array('name'), $args);

        foreach ($response["data"] as &$row) {
            $dbRole = DBRole::loadById($row['id']);

            $platforms = array();
            foreach ($dbRole->getPlatforms() as $platform)
                $platforms[] = SERVER_PLATFORMS::GetName($platform);

            if ($this->user->getAccountId() != 0)
                $usedBy =$this->db->GetOne("SELECT COUNT(*) FROM farm_roles WHERE (role_id=? OR new_role_id=?) AND farmid IN (SELECT id FROM farms WHERE env_id=?)", array($dbRole->id, $dbRole->id, $this->getEnvironmentId()));
            else
                $usedBy =$this->db->GetOne("SELECT COUNT(*) FROM farm_roles WHERE role_id=? OR new_role_id=?", array($dbRole->id, $dbRole->id));

            $status = '<span style="color:gray;">Not used</span>';
            if ($this->db->GetOne("SELECT id FROM roles_queue WHERE role_id=?", array($dbRole->id)))
                $status = '<span style="color:red;">Deleting</span>';
            elseif ($usedBy > 0)
                $status = '<span style="color:green;">In use</span>';

            $role = array(
                'name'			=> $dbRole->name,
                //'behaviors'		=> implode(", ", $dbRole->getBehaviors()),
                'id'			=> $dbRole->id,
                'client_id'		=> $dbRole->clientId,
                'env_id'		=> $dbRole->envId,
                'status'		=> $status,
                'origin'		=> $dbRole->origin,
                'os'			=> $dbRole->os,
                'tags'			=> implode(", ", $dbRole->getTags()),
                'platforms'		=> implode(", ", $platforms),
                'generation'	=> ($dbRole->generation == 2) ? 'scalarizr' : 'ami-scripts'
            );

            $behaviors = array();
            foreach ($dbRole->getBehaviors() as $b) {
                $behaviors[] = ROLE_BEHAVIORS::GetName($b);
            }
            $role['behaviors_name'] = implode(', ', $behaviors);

            $behaviors = array();
            foreach ($dbRole->getBehaviors() as $b) {
                $behaviors[] = ROLE_BEHAVIORS::GetName($b);
            }

            $role['behaviors'] = implode(', ', $dbRole->getBehaviors());

            try {
                $envId = $this->getEnvironmentId();

                $role['used_servers'] = $this->db->GetOne("SELECT COUNT(*) FROM servers WHERE role_id=? AND env_id=?",
                    array($dbRole->id, $envId)
                );
            }
            catch(Exception $e) {
                if ($this->user->getAccountId() == 0) {
                    $role['used_servers'] = $this->db->GetOne("SELECT COUNT(*) FROM servers WHERE role_id=?",
                        array($dbRole->id)
                    );

                    if ($this->db->GetOne("SELECT COUNT(*) FROM farm_roles WHERE role_id=?", array($dbRole->id)) > 0)
                        $status = '<span style="color:green;">In use</span>';

                    $role['status'] = $status;
                }
            }

            if ($dbRole->clientId == 0)
                $role["client_name"] = "Scalr";
            else
                $role["client_name"] = $this->user->getAccount()->getOwner()->fullname;

            if (! $role["client_name"])
                $role["client_name"] = "";

            $row = $role;
        }

        $this->response->data($response);
    }

    /**
    * Get information about role
    */
    public function infoAction()
    {
        $this->request->defineParams(array(
            'roleId' => array('type' => 'int')
        ));

        $roleId = $this->getParam('roleId');

        $dbRole = DBRole::loadById($roleId);

        if ($dbRole->envId != 0)
            $this->user->getPermissions()->validate($dbRole);

        $dbRole->groupName = $dbRole->getCategoryName();
        $dbRole->behaviorsList = implode(", ", $dbRole->getBehaviors());
        foreach ($dbRole->getSoftwareList() as $soft)
            $dbRole->softwareList[] = "{$soft['name']} {$soft['version']}";

        if ($dbRole->softwareList)
            $dbRole->softwareList = implode(", ", $dbRole->softwareList);
        else
            $dbRole->softwareList = "";

        $dbRole->tagsString = implode(", ", $dbRole->getTags());

        $dbRole->platformsList = array();
        foreach ($dbRole->getPlatforms() as $platform) {
            $dbRole->platformsList[] = array(
                'name' 		=> SERVER_PLATFORMS::GetName($platform),
                'locations'	=> implode(", ", $dbRole->getCloudLocations($platform))
            );
        }

        $this->response->page('ui/roles/info.js', array(
            'name' => $dbRole->name,
            'info' => get_object_vars($dbRole)
        ));
    }
}
