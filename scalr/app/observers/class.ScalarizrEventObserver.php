<?php

class ScalarizrEventObserver extends EventObserver
{

    public function OnFarmTerminated(FarmTerminatedEvent $event)
    {
        $farmRoles = $event->DBFarm->GetFarmRoles();
        foreach ($farmRoles as $farmRole) {
            // For MySQL role need to reset slave2master flag
            if ($farmRole->GetRoleObject()->hasBehavior(ROLE_BEHAVIORS::MYSQL)) {
                $farmRole->SetSetting(DBFarmRole::SETTING_MYSQL_SLAVE_TO_MASTER, 0);
            }

            if ($farmRole->GetRoleObject()->getDbMsrBehavior()) {
                $farmRole->SetSetting(Scalr_Db_Msr::SLAVE_TO_MASTER, 0);
            }
        }
    }
}
