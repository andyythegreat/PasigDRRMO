<?php
	date_default_timezone_set('Asia/Manila');

	function getCurrentDateAndTime() {
        return date('Y-m-d H:i:s', time());
    }

    function getCurrentYear() {
        return date('Y', time());
    }

    function formatTimeAgo($dateString) {
        $timestamp = strtotime($dateString);
        $currentTimestamp = time();
        $difference = $currentTimestamp - $timestamp;
        
        $intervals = array(
            array('year', 31536000),
            array('month', 2592000),
            array('week', 604800),
            array('day', 86400),
            array('hour', 3600),
            array('minute', 60),
            array('second', 1)
        );
        
        foreach ($intervals as $interval) {
            $unit = $interval[0];
            $seconds = $interval[1];
            if ($difference >= $seconds) {
                $value = floor($difference / $seconds);
                $output = $value . ' ' . $unit;
                if ($value > 1) {
                    $output .= 's';
                }
                $output .= ' ago';
                return $output;
            }
        }
        
        return 'Just now';
    }
?>