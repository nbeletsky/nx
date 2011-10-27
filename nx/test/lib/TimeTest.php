<?php

namespace nx\test\lib;

use nx\lib\Time;

class TimeTest extends \PHPUnit_Framework_TestCase {

    public function test_TimeBetween_ReturnsArray() {
        $date_1 = 'October 26, 2011 22:23:13';
        $date_2 = 'October 26, 2011 22:23:15';
        $result = Time::between($date_1, $date_2);
        $check = array(
            'days'    => 0,
            'hours'   => 0,
            'minutes' => 0,
            'seconds' => 2
        );
        $this->assertEquals($result, $check);

        $date_1 = 'October 26, 2011 22:23:13';
        $date_2 = 'October 26, 2011 22:24:15';
        $result = Time::between($date_1, $date_2);
        $check = array(
            'days'    => 0,
            'hours'   => 0,
            'minutes' => 1,
            'seconds' => 2
        );
        $this->assertEquals($result, $check);

        $date_1 = 'October 26, 2011 22:23:13';
        $date_2 = 'October 26, 2011 24:24:15';
        $result = Time::between($date_1, $date_2);
        $check = array(
            'days'    => 0,
            'hours'   => 2,
            'minutes' => 1,
            'seconds' => 2
        );
        $this->assertEquals($result, $check);

        $date_1 = 'October 26, 2011 22:23:13';
        $date_2 = 'October 27, 2011 24:24:15';
        $result = Time::between($date_1, $date_2);
        $check = array(
            'days'    => 1,
            'hours'   => 2,
            'minutes' => 1,
            'seconds' => 2
        );
        $this->assertEquals($result, $check);

        $date_1 = 'October 29, 2011 22:23:13';
        $date_2 = 'October 27, 2011 24:24:15';
        $result = Time::between($date_1, $date_2);
        $check = array(
            'days'    => 1,
            'hours'   => 21,
            'minutes' => 58,
            'seconds' => 58
        );
        $this->assertEquals($result, $check);

        $date_1 = 'October 27, 2011 24:24:15';
        $date_2 = 'October 27, 2011 24:24:15';
        $result = Time::between($date_1, $date_2);
        $check = array(
            'days'    => 0,
            'hours'   => 0,
            'minutes' => 0,
            'seconds' => 0
        );
        $this->assertEquals($result, $check);
    }

    public function test_GetWithInterval_ReturnsArray() {
        $check = array(
            '00:00', '01:00', '02:00', '03:00', '04:00', '05:00',
            '06:00', '07:00', '08:00', '09:00', '10:00', '11:00',
            '12:00', '13:00', '14:00', '15:00', '16:00', '17:00',
            '18:00', '19:00', '20:00', '21:00', '22:00', '23:00'
        );
        $result = Time::get_with_interval(60, true);
        $this->assertEquals($result, $check);

        $check = array(
            '12:00 AM', '2:00 AM', '4:00 AM', '6:00 AM', '8:00 AM',
            '10:00 AM', '12:00 PM', '2:00 PM', '4:00 PM', '6:00 PM',
            '8:00 PM', '10:00 PM'
        );
        $result = Time::get_with_interval(120, false);
        $this->assertEquals($result, $check);

        $result = Time::get_with_interval(97, false);
        $this->assertFalse($result);
    }

}
?>
