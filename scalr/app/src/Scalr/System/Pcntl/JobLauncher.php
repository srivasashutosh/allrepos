<?php
namespace Scalr\System\Pcntl;

/**
 * JobLauncher
 *
 * @version 1.0
 * @author Igor Savchenko <igor@scalr.com>
 * @since  It's refactored by Vitaliy Demidov on 31.05.2013
 */
class JobLauncher
{
    private $ProcessName;

    private $Logger;

    private $PIDDir;

    function __construct($process_classes_folder)
    {
        $this->Logger = \Logger::getLogger('JobLauncher');

        $processes = @glob("{$process_classes_folder}/class.*Process.php");

        $options = array();
        if (count($processes) > 0) {
            foreach ($processes as $process) {
                $filename = basename($process);
                $directory = dirname($process);

                if (!file_exists($directory . "/" . $filename)) {
                    throw new \Exception(sprintf(
                        "File %s does not exist.", ($directory . "/" . $filename)
                    ));
                }

                include_once $directory . "/" . $filename;

                preg_match("/class.(.*)Process.php/s", $filename, $tmp);
                $process_name = $tmp[1];

                if (class_exists("{$process_name}Process")) {
                    $reflect = new \ReflectionClass("{$process_name}Process");

                    if ($reflect) {
                        if ($reflect->implementsInterface('Scalr\\System\\Pcntl\\ProcessInterface')) {
                            $options[$process_name] = $reflect
                                ->getProperty("ProcessDescription")
                                ->getValue($reflect->newInstance())
                            ;
                        } else {
                            throw new \Exception("Class '{$process_name}Process' doesn't implement 'ProcessInterface'.", E_ERROR);
                        }
                    } else {
                        throw new \Exception("Cannot use ReflectionAPI for class '{$process_name}Process'", E_ERROR);
                    }
                } else {
                    throw new \Exception("'{$process}' does not contain definition for '{$process_name}Process'", E_ERROR);
                }
            }
        } else {
            throw new \Exception(_("No job classes found in {$process_classes_folder}"), E_ERROR);
        }

        $options["help"] = "Print this help";
        $options["piddir=s"] = "PID directory";

        $Getopt = new \Zend_Console_Getopt($options);
        try {
            $opts = $Getopt->getOptions();
        } catch (\Zend_Console_Getopt_Exception $e) {
            print "{$e->getMessage()}\n\n";
            die($Getopt->getUsageMessage());
        }

        if (in_array("help", $opts) || count($opts) == 0 || !$options[$opts[0]]) {
            print $Getopt->getUsageMessage();
            exit();
        } else {
            $this->ProcessName = $opts[0];
            if (in_array("piddir", $opts)) {
                $piddir = trim($Getopt->getOption("piddir"));
                if (substr($piddir, 0, 1) != '/') {
                    $this->PIDDir = realpath($process_classes_folder . "/" . $piddir);
                } else {
                    $this->PIDDir = $piddir;
                }
            }
        }
    }

    /**
     * Return Process name
     *
     * @return string
     */
    function GetProcessName()
    {
        return $this->ProcessName;
    }

    function Launch($max_chinds = 5, $child_exec_timeout = 0)
    {
        $proccess = new \ReflectionClass("{$this->ProcessName}Process");

        $sh = new \ReflectionClass('Scalr\\System\\Pcntl\\SignalHandler');

        $pm = new ProcessManager($sh->newInstance());

        $pm->SetChildExecLimit($child_exec_timeout);
        $pm->SetMaxChilds($max_chinds);
        if ($this->PIDDir) {
            $pm->SetPIDDir($this->PIDDir);
        }
        $pm->Run($proccess->newInstance());
    }
}
