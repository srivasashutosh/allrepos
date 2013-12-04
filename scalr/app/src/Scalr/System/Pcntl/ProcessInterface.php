<?php

namespace Scalr\System\Pcntl;

/**
 * @version 1.0
 * @author  Igor Savchenko <igor@scalr.com>
 * @since   It's refactored by Vitaliy Demidov on 31.05.2013
 */
interface ProcessInterface
{
    /**
     * On Stat Forking handler
     */
    public function OnStartForking();

    /**
     * On End forking handler
     */
    public function OnEndForking();

    /**
     * Start thread
     *
     * @param integer $id
     */
    public function StartThread($id);
}