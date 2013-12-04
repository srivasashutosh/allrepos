<?php

define("NO_TEMPLATES", 1);

require_once __DIR__ . '/../src/prepend.inc.php';

set_time_limit(0);

$ScalrUpdate = new Update20130418();
$ScalrUpdate->Run();

class Update20130418
{
    public function Run()
    {
        global $db;

        $time = microtime(true);

        $roles = $db->Execute("SELECT * FROM roles WHERE os_family IS NULL");
        while ($role = $roles->FetchRow()) {

            $family = false;
            $generation = false;
            $version = false;
            $name = false;

            $role['os'] = trim($role['os']);

            //Ubuntu 11.04 natty
            if ($role['os'] == 'Ubuntu 10.10 maverick') {
                $family = 'ubuntu';
                $generation = '10.10';
                $version = '10.10';
                $name = 'Ubuntu 10.10 Maverick';
            } elseif ($role['os'] == 'Ubuntu 11.04 natty') {
                $family = 'ubuntu';
                $generation = '11.04';
                $version = '11.04';
                $name = 'Ubuntu 11.04 Natty';
            } elseif ($role['os'] == 'Ubuntu 11.10 oneiric') {
                $family = 'ubuntu';
                $generation = '11.10';
                $version = '11.10';
                $name = 'Ubuntu 11.10 Oneiric';
            } elseif ($role['os'] == 'Ubuntu 8.04 hardy') {
                $family = 'ubuntu';
                $generation = '8.04';
                $version = '8.04';
                $name = 'Ubuntu 8.04 Hardy';
            } elseif ($role['os'] == 'Ubuntu 12.04 precise') {
                $family = 'ubuntu';
                $generation = '12.04';
                $version = '12.04';
                $name = 'Ubuntu 12.04 Precise';
            } elseif ($role['os'] == 'Oracle Linux 6.1') {
                $family = 'oel';
                $generation = '6';
                $version = '6.1';
                $name = 'Oracle Enterprise Linux Server 6.1 (Santiago)';
            } elseif ($role['os'] == 'Red Hat Enterprise Linux Server 5.4 Tikanga') {
                $family = 'redhat';
                $generation = '5';
                $version = '5.4';
                $name = 'Redhat 5.4 Tikanga';
            } elseif ($role['os'] == 'Red Hat Enterprise Linux Server 5.5 Tikanga') {
                $family = 'redhat';
                $generation = '5';
                $version = '5.5';
                $name = 'Redhat 5.5 Tikanga';
            } elseif ($role['os'] == 'Red Hat Enterprise Linux Server 5.6 Tikanga') {
                $family = 'redhat';
                $generation = '5';
                $version = '5.6';
                $name = 'Redhat 5.6 Tikanga';
            } elseif ($role['os'] == 'Red Hat Enterprise Linux Server 5.7 Tikanga') {
                $family = 'redhat';
                $generation = '5';
                $version = '5.7';
                $name = 'Redhat 5.7 Tikanga';
            } elseif ($role['os'] == 'Red Hat Enterprise Linux Server 6.0 Santiago') {
                $family = 'redhat';
                $generation = '6';
                $version = '6.0';
                $name = 'Redhat 6.0 Santiago';
            } elseif ($role['os'] == 'Red Hat Enterprise Linux Server 6.3 Santiago') {
                $family = 'redhat';
                $generation = '6';
                $version = '6.3';
                $name = 'Redhat 6.3 Santiago';
            } elseif ($role['os'] == 'debian 5.0.7') {
                $family = 'debian';
                $generation = '5';
                $version = '5.0.7';
                $name = 'Debian 5.0.7 Lenny';
            } elseif ($role['os'] == 'debian 6.0' || $role['os'] == 'debian 6.0.5' || $role['os'] == 'debian squeeze/sid') {
                $family = 'debian';
                $generation = '6';
                $version = '5.0.5';
                $name = 'Debian 6.0.5 Squeeze';
            } elseif ($role['os'] == 'CentOS 5.3' || $role['os'] == 'CentOS 5.3 Final') {
                $family = 'centos';
                $generation = '5';
                $version = '5.3';
                $name = 'CentOS 5.3 Final';
            } elseif ($role['os'] == 'CentOS 5.4' || $role['os'] == 'CentOS 5.4') {
                $family = 'centos';
                $generation = '5';
                $version = '5.4';
                $name = 'CentOS 5.4 Final';
            } elseif ($role['os'] == '2008Server' || $role['os'] == 'Windows 2008 Server') {
                $family = 'windows';
                $generation = '2008';
                $version = '2008';
                $name = 'Windows 2008 Server';
            } elseif ($role['os'] == '2008ServerR2') {
                $family = 'windows';
                $generation = '2008';
                $version = '2008';
                $name = 'Windows 2008 Server R2';
            } elseif ($role['os'] == '2003Server' || $role['os'] == 'Windows 2003 Server') {
                $family = 'windows';
                $generation = '2003';
                $version = '2003';
                $name = 'Windows 2003 Server';
            } elseif ($role['os'] == 'CentOS 5.5 Final' || $role['os'] == 'CentOS 5.5.') {
                $family = 'centos';
                $generation = '5';
                $version = '5.5';
                $name = 'CentOS 5.5 Final';
            } elseif ($role['os'] == 'CentOS 5.6 Final') {
                $family = 'centos';
                $generation = '5';
                $version = '5.6';
                $name = 'CentOS 5.6 Final';
            } elseif ($role['os'] == 'CentOS 5.7 Final') {
                $family = 'centos';
                $generation = '5';
                $version = '5.7';
                $name = 'CentOS 5.7 Final';
            } elseif ($role['os'] == 'CentOS 5.8 Final') {
                $family = 'centos';
                $generation = '5';
                $version = '5.8';
                $name = 'CentOS 5.8 Final';
            } elseif ($role['os'] == 'Ubuntu 10.04') {
                $family = 'ubuntu';
                $generation = '10.04';
                $version = '10.04';
                $name = 'Ubuntu 10.04 Lucid';
            } elseif ($role['os'] == 'CentOS 6.4 Final') {
                $family = 'centos';
                $generation = '6';
                $version = '6.4';
                $name = 'CentOS 6.4 Final';
            } elseif ($role['os'] == 'CentOS 6.3 Final') {
                $family = 'centos';
                $generation = '6';
                $version = '6.3';
                $name = 'CentOS 6.3 Final';
            } elseif ($role['os'] == 'CentOS 6.2 Final') {
                $family = 'centos';
                $generation = '6';
                $version = '6.2';
                $name = 'CentOS 6.2 Final';
            } elseif ($role['os'] == 'CentOS 6.1 Final') {
                $family = 'centos';
                $generation = '6';
                $version = '6.1';
                $name = 'CentOS 6.1 Final';
            } elseif ($role['os'] == 'Ubuntu 10.04' || $role['os'] == 'Ubuntu 10.04 lucid') {
                $family = 'ubuntu';
                $generation = '10.04';
                $version = '10.04';
                $name = 'Ubuntu 10.04 Lucid';
            } elseif ($role['os'] == 'Ubuntu 8.04') {
                $family = 'ubuntu';
                $generation = '8.04';
                $version = '8.04';
                $name = 'Ubuntu 8.04 Hardy';
            } else {

                $imageInfo = $db->GetRow("SELECT * FROM role_images WHERE role_id = ? AND os_family IS NOT NULL", array($role['id']));
                if (!$imageInfo)
                    continue;

                switch ($imageInfo['os_family']) {
                    case "ubuntu":
                        $family = 'ubuntu';
                        if ($imageInfo['os_version'] == 0 || ($imageInfo['os_version'] == 10 && $imageInfo['os_name'] == 'lucid') || $imageInfo['os_name'] == 'Ubuntu 10.04 Lucid' || $imageInfo['os_version'] == '10.04') {
                            $generation = '10.04';
                            $version = '10.04';
                            $name = 'Ubuntu 10.04 Lucid';
                        } elseif (($imageInfo['os_version'] == 10 && $imageInfo['os_name'] == 'maverick') || $imageInfo['os_version'] == '10.10') {
                            $generation = '10.10';
                            $version = '10.10';
                            $name = 'Ubuntu 10.10 Maverick';
                        } elseif (($imageInfo['os_version'] == 11 && $imageInfo['os_name'] == 'oneiric') || $imageInfo['os_version'] == '11.10' || $imageInfo['os_name'] == 'Ubuntu 11.10 Oneiric') {
                            $generation = '11.10';
                            $version = '11.10';
                            $name = 'Ubuntu 11.10 Oneiric';
                        } elseif (($imageInfo['os_version'] == 11 && $imageInfo['os_name'] == 'natty') || $imageInfo['os_version'] == '11.04') {
                            $generation = '11.04';
                            $version = '11.04';
                            $name = 'Ubuntu 11.04 Natty';
                        } elseif ($imageInfo['os_version'] == 12 || $imageInfo['os_version'] == '12.04') {
                            $generation = '12.04';
                            $version = '12.04';
                            $name = 'Ubuntu 12.04 Precise';
                        } elseif ($imageInfo['os_version'] == '12.10') {
                            $generation = '12.10';
                            $version = '12.10';
                            $name = 'Ubuntu 12.10 Quantal';
                        } elseif ($imageInfo['os_version'] == 8 || $imageInfo['os_version'] == '8.04') {
                            $generation = '8.04';
                            $version = '8.04';
                            $name = 'Ubuntu 8.04 Hardy';
                        } elseif ($imageInfo['os_version'] == 'lenny/sid') {
                            $family = 'debian';
                            $generation = '5';
                            $version = '5.0.9';
                            $name = 'Debian 5.0.9 Lenny';
                        }

                        break;
                    case "centos":
                         $family = 'centos';
                         if (($imageInfo['os_version'] == 0 && $imageInfo['os_name'] == 'CentOS 5.7 Final') || $imageInfo['os_version'] == '5.7') {
                             $generation = '5';
                             $version = '5.7';
                             $name = 'CentOS 5.7 Final';
                         } elseif (($imageInfo['os_version'] == 0 && $imageInfo['os_name'] == 'CentOS 5.5 Final')  || $imageInfo['os_version'] == '5.5') {
                             $generation = '5';
                             $version = '5.5';
                             $name = 'CentOS 5.5 Final';
                         } elseif ($imageInfo['os_version'] == '5.6') {
                             $generation = '5';
                             $version = '5.6';
                             $name = 'CentOS 5.6 Final';
                         } elseif ($imageInfo['os_version'] == '5.8') {
                             $generation = '5';
                             $version = '5.8';
                             $name = 'CentOS 5.8 Final';
                         } elseif ($imageInfo['os_version'] == 0 && $imageInfo['os_name'] == 'CentOS 5.X Final') {
                             $generation = '5';
                             $version = '5.X';
                             $name = 'CentOS 5.X Final';
                         } elseif ($imageInfo['os_version'] == 5 && ($imageInfo['os_name'] == 'CentOS 5.4 Final' || $imageInfo['os_name'] == 'Centos 5.4 Final')) {
                             $generation = '5';
                             $version = '5.4';
                             $name = 'CentOS 5.4 Final';
                         } elseif ($imageInfo['os_version'] == 5 && ($imageInfo['os_name'] == 'CentOS 5.5 Final' || $imageInfo['os_name'] == 'Centos 5.5 Final')) {
                             $generation = '5';
                             $version = '5.5';
                             $name = 'CentOS 5.5 Final';
                         } elseif ($imageInfo['os_version'] == 5 && ($imageInfo['os_name'] == 'CentOS 5.6 Final' || $imageInfo['os_name'] == 'Centos 5.6 Final')) {
                             $generation = '5';
                             $version = '5.6';
                             $name = 'CentOS 5.6 Final';
                         } elseif ($imageInfo['os_version'] == 5 && ($imageInfo['os_name'] == 'CentOS 5.7 Final' || $imageInfo['os_name'] == 'Centos 5.7 Final')) {
                             $generation = '5';
                             $version = '5.7';
                             $name = 'CentOS 5.7 Final';
                         } elseif ($imageInfo['os_version'] == 5 && ($imageInfo['os_name'] == 'CentOS 5.8 Final' || $imageInfo['os_name'] == 'Centos 5.8 Final')) {
                             $generation = '5';
                             $version = '5.8';
                             $name = 'CentOS 5.8 Final';
                         } elseif ($imageInfo['os_version'] == 5 && ($imageInfo['os_name'] == 'CentOS 5.9 Final' || $imageInfo['os_name'] == 'Centos 5.9 Final')) {
                             $generation = '5';
                             $version = '5.9';
                             $name = 'CentOS 5.9 Final';
                         } elseif ($imageInfo['os_version'] == 5 && ($imageInfo['os_name'] == 'Final' || $imageInfo['os_name'] == 'final')) {
                             $generation = '5';
                             $version = '5.X';
                             $name = 'CentOS 5.X Final';
                         }


                         elseif ($imageInfo['os_version'] == 0 && $imageInfo['os_name'] == 'CentOS 6.4 Final') {
                             $generation = '6';
                             $version = '6.4';
                             $name = 'CentOS 6.4 Final';
                         } elseif (($imageInfo['os_version'] == 0 && $imageInfo['os_name'] == 'CentOS 6.2 Final') || $imageInfo['os_version'] == '6.2') {
                             $generation = '6';
                             $version = '6.2';
                             $name = 'CentOS 6.2 Final';
                         } elseif (($imageInfo['os_version'] == 0 && $imageInfo['os_name'] == 'CentOS 6.3 Final') || $imageInfo['os_version'] == '6.3') {
                             $generation = '6';
                             $version = '6.3';
                             $name = 'CentOS 6.3 Final';
                         } elseif ($imageInfo['os_version'] == 0 && $imageInfo['os_name'] == 'CentOS 6.1 Final') {
                             $generation = '6';
                             $version = '6.1';
                             $name = 'CentOS 6.1 Final';
                         } elseif ($imageInfo['os_version'] == 0 && $imageInfo['os_name'] == 'CentOS 6.X Final') {
                             $generation = '6';
                             $version = '6.X';
                             $name = 'CentOS 6.X Final';
                         } elseif ($imageInfo['os_version'] == 0 && ($imageInfo['os_name'] == 'Final' || $imageInfo['os_name'] == 'final')) {
                             $generation = '6';
                             $version = '6.X';
                             $name = 'CentOS 6.X Final';
                         }

                        break;
                    case "red hat enterprise linux":
                    case "redhat":
                        $family = 'redhat';
                        if ($imageInfo['os_family'] == 'redhat' && $imageInfo['os_name'] == 'Santiago') {
                            $generation = '6';
                            $version = '6.3';
                            $name = 'Redhat 6.3 Santiago';
                        } elseif ($imageInfo['os_name'] == 'tikanga') {
                            $generation = '5';
                            $version = '5.7';
                            $name = 'Redhat 5.7 Tikanga';
                        } elseif ($imageInfo['os_name'] == 'Red hat enterprise linux server 6.3 Santiago') {
                            $generation = '6';
                            $version = '6.3';
                            $name = 'Redhat 6.3 Santiago';
                        } elseif ($imageInfo['os_name'] == 'Red Hat Enterprise Linux Server 6.3') {
                            $generation = '6';
                            $version = '6.3';
                            $name = 'Redhat 6.3 Santiago';
                        } elseif ($imageInfo['os_name'] == 'Red hat enterprise linux server 5.8 Tikanga') {
                            $generation = '5';
                            $version = '5.8';
                            $name = 'Redhat 5.8 Tikanga';
                        } elseif ($imageInfo['os_name'] == 'Red hat enterprise linux server 5.7 Tikanga') {
                            $generation = '5';
                            $version = '5.7';
                            $name = 'Redhat 5.7 Tikanga';
                        } elseif ($imageInfo['os_name'] == 'Red hat enterprise linux server 5.5 Tikanga') {
                            $generation = '5';
                            $version = '5.5';
                            $name = 'Redhat 5.5 Tikanga';
                        }


                        elseif ($imageInfo['os_name'] == 'Amazon Linux 2013.03') {
                            $family = 'amazon';
                            $generation = '6';
                            $version = '6.4';
                            $name = 'Amazon Linux 2013.03';
                        }  elseif ($imageInfo['os_name'] == 'Amazon Linux 2012.09') {
                            $family = 'amazon';
                            $generation = '6';
                            $version = '6.3';
                            $name = 'Amazon Linux 2012.09';
                        }

                        break;
                    case "amazon":
                        $family = 'amazon';
                        $generation = '6';
                        $version = '6.4';
                        $name = 'Amazon Linux 2013.03';
                        break;
                    case "debian":
                        $family = 'debian';
                        if ($imageInfo['os_name'] == 'Debian 5.0.7' || $imageInfo['os_version'] == '5') {
                            $generation = '5';
                            $version = '5.0.7';
                            $name = 'Debian 5.0.7 Lenny';
                        } elseif ($imageInfo['os_name'] == 'Debian 6.0.1') {
                            $generation = '6';
                            $version = '6.0.1';
                            $name = 'Debian 6.0.1 Squeeze';
                        } elseif ($imageInfo['os_name'] == 'Debian 6.0.4' || $imageInfo['os_version'] == '6.0.4' || $imageInfo['os_version'] == 'squeeze/si' || $imageInfo['os_version'] == '6') {
                            $generation = '6';
                            $version = '6.0.4';
                            $name = 'Debian 6.0.4 Squeeze';
                        } elseif ($imageInfo['os_name'] == 'Debian 6.0.5') {
                            $generation = '6';
                            $version = '6.0.5';
                            $name = 'Debian 6.0.5 Squeeze';
                        }
                        break;
                    case "oel":
                        $family = 'oel';
                        $generation = '5';
                        $version = '5.5';
                        $name = 'Oracle Enterprise Linux Server 5.5 Tikanga';
                        break;
                    case "gcel":
                        $family = 'gcel';
                        $generation = '12.04';
                        $version = '12.04';
                        $name = 'GCEL 12.04';
                        break;
                }
            }

            if ($role['os'] == '2008ServerR2' || $role['os'] == '2003Server' || $role['os'] == '2008Server' || $family == 'debian' || $family == 'ubuntu' || $family == 'centos' || $role['os'] == '' || $role['os'] == 'Unknown' || strtolower($role['os']) == strtolower($name) || $family == 'gcel' || $family == 'redhat' || $family == 'oel' || $family == 'debian') {
                if ($family && $generation && $version && $name) {
                    $db->Execute("UPDATE roles SET os = ?, os_version = ?, os_generation = ?, os_family = ? WHERE id = ?", array(
                        $name, $version, $generation, $family, $role['id']
                    ));
                }
            } else {
                print "{$role['os']} ({$name})\n";
            }
        }

        print "Done.\n";

        $t = round(microtime(true) - $time, 2);

        printf("Upgrade process took %0.2f seconds\n\n\n", $t);
    }
}