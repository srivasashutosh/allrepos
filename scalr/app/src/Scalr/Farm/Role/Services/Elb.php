<?php
namespace Scalr\Farm\Role\Services;

use Scalr\Farm\Role\FarmRoleService;

class Elb
{
    public $id,
       $type,
       $config;

    public function __construct($id = null)
    {
        $this->db = \Scalr::getDb();
        $this->type = FarmRoleService::SERVICE_AWS_ELB;
        $this->id = $id;
    }
}
