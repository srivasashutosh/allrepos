<?php

interface Scalr_System_Ipc_Worker
{

    /**
     * Gets DI Container
     *
     * @return \Scalr\DependencyInjection\Container
     */
    public function getContainer();

    /**
     * Called in parent
     * @param Scalr_Util_Queue $workQueue
     * @return Scalr_Util_Queue
     */
    public function startForking($workQueue);

    /**
     * Called in parent
     * @param $pid
     * @return unknown_type
     */
    public function childForked($pid);

    /**
     * Called in parent
     * @return
     */
    public function endForking();

    /**
     * Called in child
     */
    public function startChild();

    /**
     * Called in child
     */
    public function handleWork($message);

    /**
     * Called in child
     */
    public function endChild();

    /**
     * Called in child when SIGTERM received
     */
    public function terminate();
}