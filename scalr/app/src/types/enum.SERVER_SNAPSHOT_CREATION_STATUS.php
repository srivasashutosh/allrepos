<?
    final class SERVER_SNAPSHOT_CREATION_STATUS
    {
        /*
         Role builder statuses
         */
        const STARING_SERVER		= 'starting-server';
        const PREPARING_ENV			= 'preparing-environment';
        const INTALLING_SOFTWARE	= 'installing-software';

        const MIGRATION_STARTING_SERVER 	= 'migration-step1';
        const MIGRATION_ATTACHING_VOLUMES 	= 'migration-step2';
        const MIGRATION_COPYING_DATA 		= 'migration-step3';
        const MIGRATION_CREATING_IMAGE 		= 'migration-step4';

        const PENDING 				= 'pending';
        const PREPARING				= 'preparing';
        const IN_PROGRESS 			= 'in-progress';
        const REPLACING_SERVERS		= 'replacing-servers';
        const CREATING_ROLE			= 'creating-role';
        const SUCCESS				= 'success';
        const FAILED				= 'failed';
        const CANCELLED				= 'cancelled';
    }
?>