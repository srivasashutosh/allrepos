<?php

class Scalr_Util_DateTime
{
    public static function convertDateTime(DateTime $dt, $remoteTz = NULL)
    {
        if (is_null($remoteTz)) {
            $remoteTz = date_default_timezone_get();
            if (! is_string($remoteTz))
                return $dt;
        }

        if (! $remoteTz instanceof DateTimeZone)
            $remoteTz = new DateTimeZone($remoteTz);

        $dt->setTimezone($remoteTz);
        return $dt;
    }

    /**
     * Converts Time according to timezone settings of current user.
     *
     * @param   DateTime|string|int  $value  DateTime object or Unix Timestamp or string that represents time.
     * @param   string               $format  Format
     * @return  string               Returns updated time in given format.
     */
    public static function convertTz($value, $format = 'M j, Y H:i:s')
    {
        if (is_integer($value)) {
            $value = "@{$value}";
        }

        if ($value instanceof DateTime) {
            $dt = $value;
        } else {
            $dt = new DateTime($value);
        }

        if ($dt && $dt->getTimestamp()) {
            if (Scalr_UI_Request::getInstance()->getUser()) {
                $timezone = Scalr_UI_Request::getInstance()->getUser()->getSetting(Scalr_Account_User::SETTING_UI_TIMEZONE);
                if (! $timezone) {
                    $timezone = 'UTC';
                }

                self::convertDateTime($dt, $timezone);
            }

            return $dt->format($format);
        } else
            return NULL;
    }

    public static function getTimezones()
    {
        $timezones = array();
        foreach (DateTimeZone::listAbbreviations() as $timezoneAbbreviations) {
            foreach ($timezoneAbbreviations as $value) {
                if (preg_match( '/^(America|Arctic|Asia|Atlantic|Europe|Indian|Pacific|Australia|UTC)/', $value['timezone_id']))
                    $timezones[$value['timezone_id']] = $value['offset'];
            }
        }

        @ksort($timezones);
        return array_keys($timezones);
    }

    public static function findTimezoneByOffset($offset)
    {
        foreach (DateTimeZone::listAbbreviations() as $timezoneAbbreviations) {
            foreach ($timezoneAbbreviations as $value) {
                if (preg_match('/^(America|Antartica|Arctic|Asia|Atlantic|Europe|Indian|Pacific|Australia|UTC)/', $value['timezone_id']) && $value['offset'] == $offset)
                    return $value['timezone_id'];
            }
        }
    }

    /**
     * Correct time with current timezone offset
     *
     * @param   integer    $time Time to convert
     * @param   float      $tz_offset timezone offset in hours
     * @return  int        Returns unix timestamp
     */
    public static function correctTime($time = 0, $tz_offset = null)
    {
        if (!is_numeric($time)) {
            $time = strtotime($time);
        }
        if (!$time) {
            $time = time();
        }
        return (is_null($tz_offset) ? $time : $time - date('Z') + $tz_offset * 3600);
    }

    /**
     * Gets a slightly more fuzzy time string. such as: yesterday at 3:51pm
     *
     * @param  integer|string $time      Time
     * @param  integer        $tz_offset optional A timezone offset
     * @return string
     */
    public static function getFuzzyTime($time = 0, $tz_offset = null)
    {
        $time = self::correctTime($time, $tz_offset);
        $now = self::correctTime(0, $tz_offset);

        $sodTime = mktime(0, 0, 0, date('m', $time), date('d', $time), date('Y', $time));
        $sodNow = mktime(0, 0, 0, date('m', $now), date('d', $now), date('Y', $now));

        if ($sodNow == $sodTime) {
            return 'today at ' . date('g:ia', $time); // check 'today'
        } else if (($sodNow - $sodTime) <= 86400) {
            return 'yesterday at ' . date('g:ia', $time); // check 'yesterday'
        } else if (($sodNow - $sodTime) <= 432000) {
            return date('l \a\\t g:ia', $time); // give a day name if within the last 5 days
        } else if (date('Y', $now) == date('Y', $time)) {
            return date('M j \a\\t g:ia', $time); // miss off the year if it's this year
        } else {
            return date('M j, Y \a\\t g:ia', $time); // return the date as normal
        }
    }

    public static function getFuzzyTimeString($value)
    {
        if (is_integer($value)) {
            $value = "@{$value}";
        }

        if (!($value instanceof DateTime)) {
            $value = new DateTime($value);
        }
        $time = $value->getTimestamp();

        if ($time) {
            $now = time();
            $sodTime = mktime(0, 0, 0, date('m', $time), date('d', $time), date('Y', $time));
            $sodNow  = mktime(0, 0, 0, date('m', $now), date('d', $now), date('Y', $now));

            $diff = $sodNow - $sodTime;
            if ($sodNow == $sodTime) {// check 'today'
                return 'today at ' . Scalr_Util_DateTime::convertTz($time, 'g:ia');
            } else if ($diff <= 86400) {// check 'yesterday'
                return 'yesterday at ' . Scalr_Util_DateTime::convertTz($time, 'g:ia');
            } else if ($diff <= 604800) { //within last week
                return floor($diff/86400).' days ago';
            } else if ($diff <= 2419200) {//within last month
                $week = floor($diff/604800);
                return $week.' week'.($week>1?'s':'').' ago';
            } else if (date('Y', $now) == date('Y', $time)) {
                return Scalr_Util_DateTime::convertTz($time, 'M j \a\\t g:ia'); // miss off the year if it's this year
            } else {
                return Scalr_Util_DateTime::convertTz($time, 'M j, Y'); // return the date as normal
            }

        } else
            return NULL;
    }

    /**
     * Converts a Unix timestamp or date/time string to a human-readable
     * format, such as '1 day, 2 hours, 42 mins, and 52 secs'
     *
     * Based on the word_time() function from PG+ (http://pgplus.ewtoo.org)
     *
     * @param  integer|string $time      Timeout
     * @param  bool           $show_secs optional Should it show the seconds
     * @return string         Returns human readable timeout
     */
    public static function getHumanReadableTimeout($time = 0, $show_secs = true)
    {
        if (!is_numeric($time)) {
            $time = strtotime($time);
        }
        if ($time == 0) {
            return 'Unknown';
        } else {
            if ($time < 0) {
                $neg = 1;
                $time = 0 - $time;
            } else {
                $neg = 0;
            }

            $days = floor($time / 86400);

            $hrs = ($time / 3600) % 24;

            $mins = ($time / 60) % 60;

            if ($show_secs) $secs = $time % 60;

            $timestring = '';
            if ($neg) {
                $timestring .= 'negative ';
            }
            if ($days) {
                $timestring .= "$days day" . ($days == 1 ? '' : 's');
                if ($hrs || $mins || $secs) {
                    $timestring .= ', ';
                }
            }
            if ($hrs) {
                $timestring .= "$hrs hour" . ($hrs == 1 ? '' : 's');
                if ($mins && $secs) {
                    $timestring .= ', ';
                }
                if (($mins && !$secs) || (!$mins && $secs)) {
                    $timestring .= ' and ';
                }
            }
            if ($mins) {
                $timestring .= "$mins min" . ($mins == 1 ? '' : 's');
                if ($mins && $secs) {
                    $timestring .= ', ';
                }
                if ($secs) {
                    $timestring .= ' and ';
                }
            }
            if ($secs) {
                $timestring .= "$secs sec" . ($secs == 1 ? '' : 's');
            }

            return $timestring;
        }
    }
}
