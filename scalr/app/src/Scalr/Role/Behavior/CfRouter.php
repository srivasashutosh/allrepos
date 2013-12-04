<?php
    class Scalr_Role_Behavior_CfRouter extends Scalr_Role_Behavior implements Scalr_Role_iBehavior
    {
        public function getSecurityRules()
        {
            return array(
                "tcp:80:80:0.0.0.0/0"
            );
        }
    }