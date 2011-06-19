<?php

/**
 * NX
 *
 * @author    Nick Sinopoli <NSinopoli@gmail.com>
 * @copyright Copyright (c) 2011, Nick Sinopoli
 * @license   http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace nx\lib;

/*
 *  The `Time` class contains methods that deal with
 *  presentation of data involving time.
 *
 *  @package lib
 */
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

   /**
    *  Calculates the amount of time remaining between now and
    *  a date in the future.
    *
    *  @param string $end_date       The date in the future.
    *  @return string
    */
    public static function remaining($end_date) {
        $time = strtotime($end_date) - time();
        if ( $time < 0 ) {
            return false;
        }

        if ( ($time >= 0) && ($time <= 59) ) {
            $time_left = $time . 's';
        } elseif ( ($time >= 60) && ($time <= 3599) ) {
            $total_min = $time / 60;
            $min = floor($total_min);

            $sec = floor(($total_min - $min) * 60);

            $time_left = $min . 'm ' . $sec . 's';
        } elseif ( ($time >= 3600) && ($time <= 86399) ) {
            $total_hour = $time / 3600;
            $hour = floor($total_hour);

            $min = floor(($total_hour - $hour) * 60);

            $time_left = $hour . 'h ' . $min . 'm';
        } elseif ( $time >= 86400 ) {
            $total_day = $time / 86400;
            $day = floor($total_day);

            $total_hour = $total_day - $day;
            $hour = floor(($total_hour * 24));

            $total_min = ($total_hour * 24) - $hour;
            $min = floor(($total_min * 60));

            $time_left = $day . 'd ' . $hour . 'h ' . $min . 'm';
        }
        return $time_left;
    }

}

?>
