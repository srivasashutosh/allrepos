<?php

class RoleOptionChangedEvent extends Event
{

    public $DBFarmRole;

    public $OptionName;

    public function __construct(DBFarmRole $DBFarmRole, $option_name)
    {
        parent::__construct();
        $this->DBFarmRole = $DBFarmRole;
        $this->OptionName = $option_name;
    }

    public function getTextDetails()
    {
        return "Option {$this->OptionName} for farm role {$this->DBFarmRole->ID} changed";
    }
}
