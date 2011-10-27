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
     *  Calculates the amount of time between two dates.
     *
     *
     *  @param string $date_1       The first date.
     *  @param string $date_2       The second date.  Defaults to now.
     *  @access public
     *  @return array
     */
    public static function between($date_1, $date_2 = null) {
        if ( is_null($date_2) ) {
            $date_2 = time();
        } else {
            $date_2 = strtotime($date_2);
        }
        $time = abs(strtotime($date_1) - $date_2);

        $days = floor($time / 86400);
        $remainder = $time % 86400;
        $hours = floor($remainder / 3600);
        $remainder = $remainder % 3600;
        $minutes = floor($remainder / 60);
        $seconds = $remainder % 60;
        return compact('days', 'hours', 'minutes', 'seconds');
    }

   /**
    *  Returns an array of times separated by a constant interval.
    *
    *  @param int $interval          The interval (in minutes) between
    *                                each time.
    *  @param bool $military         Whether or not to output time using
    *                                24-hour format.
    *  @access public
    *  @return array
    */
    public static function get_with_interval($interval = 15, $military = false) {
        if ( $interval % 60 !== 0 ) {
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
