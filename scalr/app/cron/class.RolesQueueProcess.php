<?php

class RolesQueueProcess implements \Scalr\System\Pcntl\ProcessInterface
{
    public $ThreadArgs;
    public $ProcessDescription = "Roles queue";
    public $Logger;
    public $IsDaemon;

    public function __construct()
    {
        // Get Logger instance
        $this->Logger = Logger::getLogger(__CLASS__);
    }

    public function OnStartForking()
    {
        $db = \Scalr::getDb();

        $roles = $db->GetAll("SELECT * FROM roles_queue WHERE `action` = 'remove'");
        foreach ($roles as $role)
        {
            try {
                $dbRole = DBRole::loadById($role['role_id']);
                $dbRole->remove(true);
            } catch (Exception $e) {
                print $e->getMessage()."\n";
            }
        }
    }

    public function OnEndForking()
    {

    }

    public function StartThread($farminfo)
    {

    }
}
