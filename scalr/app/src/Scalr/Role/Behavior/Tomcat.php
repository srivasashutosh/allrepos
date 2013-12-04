<?php
    class Scalr_Role_Behavior_Tomcat extends Scalr_Role_Behavior implements Scalr_Role_iBehavior
    {
        public function __construct($behaviorName)
        {
            parent::__construct($behaviorName);
        }

        public function getSecurityRules()
        {
            return array(
                "tcp:80:80:0.0.0.0/0",
                "tcp:443:443:0.0.0.0/0",
                "tcp:8080:8080:0.0.0.0/0",
                "tcp:8443:8443:0.0.0.0/0"
            );
        }
    }