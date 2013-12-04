<?php
    class Modules_Platforms_Openstack_Adapters_Status implements IModules_Platforms_Adapters_Status
    {
        private $platformStatus;

        /*
        ACTIVE. The server is active and ready to use.
        BUILD. The server is being built.
        DELETED. The server was deleted. The list servers API operation does not show servers with a status of DELETED. To list deleted servers, use the changes-since parameter. See Section 1.6, “Efficient Polling with the Changes-Since Parameter”.
        ERROR. The requested operation failed and the server is in an error state.
        HARD_REBOOT. The server is going through a hard reboot. A hard reboot power cycles your server, which performs an immediate shutdown and restart. See Section 2.3.2, “Reboot Server”.
        MIGRATING. The server is being moved from one physical node to another physical node. Server migration is a Rackspace extension.
        PASSWORD. The password for the server is being changed. See Section 2.3.1, “Change Administrator Password”.
        REBOOT. The server is going through a soft reboot. During a soft reboot, the operating system is signaled to restart, which allows for a graceful shutdown and restart of all processes. See Section 2.3.2, “Reboot Server”.
        REBUILD. The server is being rebuilt from an image. See Section 2.3.3, “Rebuild Server”.
        RESCUE. The server is in rescue mode. Rescue mode is a Rackspace extension. See Section 3.4, “Rescue Mode Extension”.
        RESIZE. The server is being resized and is inactive until the resize operation completes.See Section 2.3.4, “Resize Server”.
        REVERT_RESIZE. A resized or migrated server is being reverted to its previous size. The destination server is being cleaned up and the original source server is restarting. For a server that was resized, see Section 2.3.4, “Resize Server”. Server migration is a Rackspace extension.
        SUSPENDED. The server is inactive, either by request or necessity. Review support tickets or contact Rackspace support to determine why the server is in this state.
        UNKNOWN. The server is in an unknown state. Contact Rackspace support.
        VERIFY_RESIZE. The server is waiting for the resize operation to be confirmed so that the original server can be removed.
         */

        private $runningStatuses = array(
            'ACTIVE', 'REBUILD','SUSPENDED','REVERT_RESIZE', 'PREP_RESIZE', 'RESIZE', 'VERIFY_RESIZE', 'PASSWORD', 'RESCUE', 'REBOOT',
            'HARD_REBOOT', 'SHARE_IP', 'SHARE_IP_NO_CONFIG', 'DELETE_IP', 'MIGRATING'
        );

        public static function load($status)
        {
            return new Modules_Platforms_Openstack_Adapters_Status($status);
        }

        public function __construct($status)
        {
            $this->platformStatus = $status;
        }

        public function getName()
        {
            return $this->platformStatus;
        }

        public function isRunning()
        {
            return (in_array($this->platformStatus, $this->runningStatuses) !== false);
        }

        public function isPending()
        {
            return $this->platformStatus == 'BUILD' ? true : false;
        }

        public function isTerminated()
        {
            return (in_array($this->platformStatus, array('DELETED', 'not-found', 'UNKNOWN', 'ERROR')) !== false);
        }

        public function isSuspended()
        {
            return ($this->platformStatus == 'SUSPENDED');
        }

        public function isPendingSuspend()
        {
            //
        }

        public function isPendingRestore()
        {
            //
        }
    }