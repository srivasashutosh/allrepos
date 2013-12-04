<?php
    class Scalr_Role_Behavior_MariaDB extends Scalr_Role_DbMsrBehavior implements Scalr_Role_iBehavior
    {
        public function __construct($behaviorName)
        {
            parent::__construct($behaviorName);
        }

        public function getSecurityRules()
        {
            return array(
                "tcp:3306:3306:0.0.0.0/0"
            );
        }
    }