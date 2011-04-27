<?php

namespace nx\lib;

class Time {

   /**
    *  Returns an array of times separated by a constant interval.
    *
    *  @param int $interval          The interval between each time in the list.
    *  @param bool $military         Whether or not to output time using 24-hour format.
    *  @access public
    *  @return array
    */
    public static function get_with_interval($interval = 15, $military = false) {
        if ( 60 % interval !== 0 ) {
            return false;
        }

        $list = array();
        // Ensure we're beginning at midnight
        $original_timezone = date_default_timezone_get();
        date_default_timezone_set('UTC');
        $time = strtotime(date('m-j-Y'));
        $steps = (1440 / $interval) - 1; // (1440 = minutes in a day)
        for ($i = 0; $i <= $steps; $i++) {
            $list[] = ( $military ) ? date('H:i', $time) : date('g:i A', $time);
            $time += ($interval * 60);
        }
        date_default_timezone_set($original_timezone);
        return $list;
    }

}

?>
