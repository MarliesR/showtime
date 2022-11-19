<?php
defined('MOODLE_INTERNAL') || die();
// Default session time limit in seconds.
define('BLOCK_DEDICATION_DEFAULT_SESSION_LIMIT', 60 * 60);
// Ignore sessions with a duration less than defined value in seconds.
define('BLOCK_DEDICATION_IGNORE_SESSION_TIME', 59);
// Default regeneration time in seconds.
define('BLOCK_DEDICATION_DEFAULT_REGEN_TIME', 60 * 15);

class block_showtime_manager{

    protected $limit;
    protected $userid;

    public function __construct ($limit){
        $this->limit = $limit;

    }

    public function get_student_moodletime($user, $Semesterstart,$simple = false ){
        global $DB;

        //Semesterstartdatum in Unix-Timestamp umwandeln: https://www.confirado.de/tools/timestamp-umrechner.html
        //$Semesterstart = '1645480800'; // = 22.02.2022 (Start des 4. Semesters)

        $where = 'userid =:userid AND timecreated >= '. $Semesterstart;
        $params = array('userid' => $user->id);

        $logs = block_showtime_utils::get_events_select($where, $params);

        if ($simple){
            $total = 0;

            if ($logs) {
                $previouslog = array_shift($logs);
                $previouslogtime = $previouslog->time;
                $sessionstart = $previouslogtime;

                foreach ($logs as $log) {
                    if (($log->time - $previouslogtime) > $this->limit) {
                        $dedication = $previouslogtime - $sessionstart;
                        $total += $dedication;
                        $sessionstart = $log->time;
                    }
                    $previouslogtime = $log->time;
                }
                $dedication = $previouslogtime - $sessionstart;
                $total += $dedication;
            }

            return $total;
        } else {
            // Return user sessions with details.
            $rows = array();

            if ($logs) {
                $previouslog = array_shift($logs);
                $previouslogtime = $previouslog->time;
                $sessionstart = $previouslogtime;
                $ips = array($previouslog->ip => true);

                foreach ($logs as $log) {
                    if (($log->time - $previouslogtime) > $this->limit) {
                        $dedication = $previouslogtime - $sessionstart;

                        // Ignore sessions with a really short duration.
                        if ($dedication > BLOCK_DEDICATION_IGNORE_SESSION_TIME) {
                            $rows[] = (object) array('start_date' => $sessionstart, 'dedicationtime' => $dedication, 'ips' => array_keys($ips));
                            $ips = array();
                        }
                        $sessionstart = $log->time;
                    }
                    $previouslogtime = $log->time;
                    $ips[$log->ip] = true;
                }

                $dedication = $previouslogtime - $sessionstart;

                // Ignore sessions with a really short duration.
                if ($dedication > BLOCK_DEDICATION_IGNORE_SESSION_TIME) {
                    $rows[] = (object) array('start_date' => $sessionstart, 'dedicationtime' => $dedication, 'ips' => array_keys($ips));
                }
            }

            return $rows;
        }

        

    }



}

class block_showtime_utils {

    public static $logstores = array('logstore_standard', 'logstore_legacy');

    public static function get_events_select($selectwhere, array $params) {
        $return = array();

        static $allreaders = null;

        if (is_null($allreaders)) {
            $allreaders = get_log_manager()->get_readers();
        }

        $processedreaders = 0;

        foreach (self::$logstores as $name) {
            if (isset($allreaders[$name])) {
                $reader = $allreaders[$name];
                $events = $reader->get_events_select($selectwhere, $params, 'timecreated ASC', 0, 0);
                foreach ($events as $event) {
                    // Note: see \core\event\base to view base class of event.
                    $obj = new stdClass();
                    $obj->time = $event->timecreated;
                    $obj->ip = $event->get_logextra()['ip'];
                    $return[] = $obj;
                }
                if (!empty($events)) {
                    $processedreaders++;
                }
            }
        }

        // Sort mixed array by time ascending again only when more of a reader has added events to return array.
        if ($processedreaders > 1) {
            usort($return, function($a, $b) {
                return $a->time > $b->time;
            });
        }

        return $return;
    }

    public static function format_showtime($totalsecs) {
        $totalsecs = abs($totalsecs);

        $str = new stdClass();
        $str->hour = get_string('hour');
        $str->hours = get_string('hours');
        $str->min = get_string('min');
        $str->mins = get_string('mins');
        $str->sec = get_string('sec');
        $str->secs = get_string('secs');

        $hours = floor($totalsecs / HOURSECS);
        $remainder = $totalsecs - ($hours * HOURSECS);
        $mins = floor($remainder / MINSECS);
        $secs = round($remainder - ($mins * MINSECS), 2);

        $ss = ($secs == 1) ? $str->sec : $str->secs;
        $sm = ($mins == 1) ? $str->min : $str->mins;
        $sh = ($hours == 1) ? $str->hour : $str->hours;

        $ohours = '';
        $omins = '';
        $osecs = '';

        if ($hours) {
            $ohours = $hours . ' ' . $sh;
        }
        if ($mins) {
            $omins = $mins . ' ' . $sm;
        }
        if ($secs) {
            $osecs = $secs . ' ' . $ss;
        }

        if ($hours) {
            return trim($ohours . ' ' . $omins);
        }
        if ($mins) {
            return trim($omins . ' ' . $osecs);
        }
        if ($secs) {
            return $osecs;
        }
        return get_string('none');
    }
}