<?php

class RotateLogsProcess implements \Scalr\System\Pcntl\ProcessInterface
{
    public $ThreadArgs;
    public $ProcessDescription = "Rotate logs table";
    public $Logger;
    public $IsDaemon;

    public function __construct()
    {
        // Get Logger instance
        $this->Logger = Logger::getLogger(__CLASS__);
    }

    public function OnStartForking()
    {
        $db = \Scalr::getDb();

        // Clear old instances log
        $oldlogtime = mktime(date("H"), date("i"), date("s"), date("m"), date("d")-10, date("Y"));
        $db->Execute("DELETE FROM logentries WHERE `time` < {$oldlogtime}");
        print "DELETE FROM logentries WHERE `time` < {$oldlogtime}\n";
        sleep(60);

        $oldlogtime = date("Y-m-d H:i:s", mktime(date("H"), date("i"), date("s"), date("m"), date("d")-7, date("Y")));
        $db->Execute("DELETE FROM scripting_log WHERE `dtadded` < '{$oldlogtime}'");
        print "DELETE FROM scripting_log WHERE `dtadded` < '{$oldlogtime}'\n";
        sleep(60);

        $oldlogtime = date("Y-m-d H:i:s", mktime(date("H"), date("i"), date("s"), date("m")-2, date("d"), date("Y")));
        $db->Execute("DELETE FROM events WHERE `dtadded` < '{$oldlogtime}'");
        print "DELETE FROM events WHERE `dtadded` < '{$oldlogtime}'\n";
        sleep(60);

        $oldlogtime = date("Y-m-d H:i:s", mktime(date("H"), date("i"), date("s"), date("m"), date("d")-10, date("Y")));
        $db->Execute("DELETE FROM messages WHERE type='out' AND status='1' AND `dtlasthandleattempt` < '{$oldlogtime}'");
        print "m1\n";
        sleep(60);
        $db->Execute("DELETE FROM messages WHERE type='out' AND status='3' AND `dtlasthandleattempt` < '{$oldlogtime}'");
        print "m2\n";
        sleep(60);
        $oldlogtime = date("Y-m-d H:i:s", mktime(date("H"), date("i"), date("s"), date("m"), date("d")-20, date("Y")));
        $db->Execute("DELETE FROM messages WHERE type='in' AND status='1' AND `dtlasthandleattempt` < '{$oldlogtime}'");
        print "m3\n";
        sleep(60);

        print "SYSLOG\n";
        //Clear old scripting events
        $year = date("Y");
        $month = date("m", mktime(date("H"), date("i"), date("s"), date("m")-1, date("d"), date("Y")));
        $db->Execute("DELETE FROM  `farm_role_scripts` WHERE ismenuitem='0' AND event_name LIKE  'CustomEvent-{$year}{$month}%'");
        $db->Execute("DELETE FROM  `farm_role_scripts` WHERE ismenuitem='0' AND event_name LIKE  'APIEvent-{$year}{$month}%'");

        // Rotate syslog
        if ($db->GetOne("SELECT COUNT(*) FROM syslog") > 1000000)
        {
            $dtstamp = date("dmY");
            $db->Execute("CREATE TABLE syslog_{$dtstamp} (id INT NOT NULL AUTO_INCREMENT,
                          PRIMARY KEY (id))
                          ENGINE=MyISAM SELECT dtadded, message, severity, transactionid FROM syslog;");
            $db->Execute("TRUNCATE TABLE syslog");
            $db->Execute("OPTIMIZE TABLE syslog");
            $db->Execute("TRUNCATE TABLE syslog_metadata");
            $db->Execute("OPTIMIZE TABLE syslog_metadata");

            $this->Logger->debug("Log rotated. New table 'syslog_{$dtstamp}' created.");
        }
    }

    public function OnEndForking()
    {

    }

    public function StartThread($farminfo)
    {

    }
}
