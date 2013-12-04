<?php

interface IPlatformModule
{
    public function getLocations();

    public function LaunchServer(DBServer $DBServer, Scalr_Server_LaunchOptions $launchOptions = null);

    /**
     * Terminates specified server
     *
     * @param   DBServer $DBServer The DB Server object
     * @return  bool     Returns TRUE on success or throws an Exception otherwise.
     * @throws  Exception
     */
    public function TerminateServer(DBServer $DBServer);

    /**
     * Reboots specified server
     *
     * @param   DBServer $DBServer The DB Server object
     * @return  bool     Returns TRUE on success or throws an Exception otherwise.
     * @throws  Exception
     */
    public function RebootServer(DBServer $DBServer);

    public function CreateServerSnapshot(BundleTask $BundleTask);

    /**
     * Checks server snapshot status
     *
     * @param   BundleTask $BundleTask The Bundle Task object
     */
    public function CheckServerSnapshotStatus(BundleTask $BundleTask);

    /**
     * Removes servers snapshot
     *
     * @param   DBRole $DBRole The DB Role object
     */
    public function RemoveServerSnapshot(DBRole $DBRole);

    public function GetServerExtendedInformation(DBServer $DBServer);

    /**
     * Gets console output for the specified server
     *
     * @param   DBServer $DBServer The DB Server object
     * @return  string   Returns console output if it is not empty otherwise it returns FALSE.
     *                   If server can not be found it throws an exception.
     * @throws  Exception
     */
    public function GetServerConsoleOutput(DBServer $DBServer);

    /**
     * Gets the status for the specified DB Server
     *
     * @param   DBServer                           $DBServer  DB Server object
     * @return  IModules_Platforms_Adapters_Status $status Returns the status
     */
    public function GetServerRealStatus(DBServer $DBServer);

    /**
     * Gets IP Addresses for the specified DB Server
     *
     * @param   DBServer     $DBServer  DB Server object
     * @return  array        Returns array looks like array(
     *                           'localIp'  => Local-IP,
     *                           'remoteIp' => Remote-IP,
     *                       )
     */
    public function GetServerIPAddresses(DBServer $DBServer);

    public function IsServerExists(DBServer $DBServer);

    public function PutAccessData(DBServer $DBServer, Scalr_Messaging_Msg $message);

    public function ClearCache();

    public function GetServerID(DBServer $DBServer);

    public function GetServerCloudLocation(DBServer $DBServer);

    public function GetServerFlavor(DBServer $DBServer);
}
