<?php

namespace Scalr\System\Pcntl;

/**
 * SignalHandler
 *
 * @version 1.0
 * @author Igor Savchenko <igor@scalr.com>
 * @since  It's refactored by Vitaliy Demidov on 31.05.2013
 */
class SignalHandler
{
    /**
     * Processmanager instance
     *
     * @var ProcessManager
     */
    public $ProcessManager;

    private $Logger;

    /**
     * Constructor
     */
    function __construct()
    {
        $this->Logger = \Logger::getLogger('SignalHandler');

    	if (!function_exists("pcntl_signal")) {
            throw new \Exception("Function pcntl_signal() is not found. PCNTL must be enabled in PHP.", E_ERROR);
    	}
    }

    /**
     * Set handlers to signals
     */
    final public function SetSignalHandlers()
    {
    	$this->Logger->debug("Begin add handler to signals...");

        foreach (array('SIGCHLD', 'SIGTERM', 'SIGABRT', 'SIGUSR2') as $sig) {
            $res = @pcntl_signal(constant($sig), array($this, "HandleSignals"));
            $this->Logger->debug("Handle {$sig} = {$res}");
        }
    }

    /**
     * Signals handler function
     *
     * @param integer $signal
     */
    final public function HandleSignals($signal)
    {
        $this->Logger->debug("HandleSignals received signal {$signal}");

        if ($signal == SIGUSR2) {
            $this->Logger->debug("Recived SIGUSR2 from one of childs");
            $this->ProcessManager->PIDs = array();
            $this->ProcessManager->ForkThreads();
            return;
        }

        $pid = @pcntl_wait($status, WNOHANG | WUNTRACED);

        if ($pid > 0) {
            $this->Logger->debug(
                "Application received signal {$signal} from child with PID# {$pid} (Exit code: {$status})"
            );
            foreach ((array) $this->ProcessManager->PIDs as $ipid => $ipid_info) {
                if ($ipid == $pid) {
                    unset($this->ProcessManager->PIDs[$ipid]);

                    if ($this->ProcessManager->PIDDir) {
                        $this->Logger->debug("Delete thread PID file $ipid");
                        @unlink($this->ProcessManager->PIDDir . "/" . $ipid);
                    }

                    $known_child = true;
                    break;
                }
            }

            if ($known_child) {
                $this->ProcessManager->ForkThreads();
            } else {
                $this->Logger->debug("Signal received from unknown child.");
            }
        }
    }

    /**
     * Add new handler on signal
     *
     * @param integer $signal
     * @param mixed $handler
     */
    final public function AddHandler($signal, $handler = false)
    {
        $signal = (int) $signal;

        if (!$handler) {
            $handler = array($this, "HandleSignals");
        }

        @pcntl_signal($signal, $handler);

        $this->Logger->debug("Added new handler on signal {$signal}.");
    }
}
