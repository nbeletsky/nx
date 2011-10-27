<?php

namespace nx\test\lib;

use nx\lib\Validator;

class ValidatorTest extends \PHPUnit_Framework_TestCase {

    public function test_Alphanumeric_ReturnsBool() {
        $check = '234gdd';
        $this->assertTrue(Validator::alphanumeric($check));

        $check = 'свободными';
        $this->assertTrue(Validator::alphanumeric($check));

        $check = '2$34gdd';
        $this->assertFalse(Validator::alphanumeric($check));

        $check = "\n";
        $this->assertFalse(Validator::alphanumeric($check));
    }

    public function test_Date_ReturnsBool() {
        $format = 'dmy';
        $check = '20 10 2011';
        $this->assertTrue(Validator::date($check, compact('format')));

        $check = '20.10.2011';
        $this->assertTrue(Validator::date($check, compact('format')));

        $check = '20-10-2011';
        $this->assertTrue(Validator::date($check, compact('format')));

        $check = '20/10/2011';
        $this->assertTrue(Validator::date($check, compact('format')));

        $check = '29/02/2011';
        $this->assertFalse(Validator::date($check, compact('format')));

        $check = '20 10 11';
        $this->assertTrue(Validator::date($check, compact('format')));

        $check = '20.10.11';
        $this->assertTrue(Validator::date($check, compact('format')));

        $check = '20-10-11';
        $this->assertTrue(Validator::date($check, compact('format')));

        $check = '20/10/11';
        $this->assertTrue(Validator::date($check, compact('format')));

        $check = '29/02/11';
        $this->assertFalse(Validator::date($check, compact('format')));


        $format = 'mdy';
        $check = '10 20 2011';
        $this->assertTrue(Validator::date($check, compact('format')));

        $check = '10.20.2011';
        $this->assertTrue(Validator::date($check, compact('format')));

        $check = '10-20-2011';
        $this->assertTrue(Validator::date($check, compact('format')));

        $check = '10/20/2011';
        $this->assertTrue(Validator::date($check, compact('format')));

        $check = '02/29/2011';
        $this->assertFalse(Validator::date($check, compact('format')));

        $check = '10 20 11';
        $this->assertTrue(Validator::date($check, compact('format')));

        $check = '10.20.11';
        $this->assertTrue(Validator::date($check, compact('format')));

        $check = '10-20-11';
        $this->assertTrue(Validator::date($check, compact('format')));

        $check = '10/20/11';
        $this->assertTrue(Validator::date($check, compact('format')));

        $check = '02/29/11';
        $this->assertFalse(Validator::date($check, compact('format')));


        $format = 'ymd';
        $check = '2011 10 20';
        $this->assertTrue(Validator::date($check, compact('format')));

        $check = '2011.10.20';
        $this->assertTrue(Validator::date($check, compact('format')));

        $check = '2011-10-20';
        $this->assertTrue(Validator::date($check, compact('format')));

        $check = '2011/10/20';
        $this->assertTrue(Validator::date($check, compact('format')));

        $check = '2011/02/29';
        $this->assertFalse(Validator::date($check, compact('format')));

        $check = '11 10 20';
        $this->assertTrue(Validator::date($check, compact('format')));

        $check = '11.10.20';
        $this->assertTrue(Validator::date($check, compact('format')));

        $check = '11-10-20';
        $this->assertTrue(Validator::date($check, compact('format')));

        $check = '11/10/20';
        $this->assertTrue(Validator::date($check, compact('format')));

        $check = '11/02/29';
        $this->assertFalse(Validator::date($check, compact('format')));


        $format = 'dMy';
        $check = '20 October 2011';
        $this->assertTrue(Validator::date($check, compact('format')));

        $check = '20 Oct 2011';
        $this->assertTrue(Validator::date($check, compact('format')));

        $check = '20 Octob 2011';
        $this->assertFalse(Validator::date($check, compact('format')));


        $format = 'Mdy';
        $check = 'October 20, 2011';
        $this->assertTrue(Validator::date($check, compact('format')));

        $check = 'October 20 2011';
        $this->assertTrue(Validator::date($check, compact('format')));

        $check = 'Oct 20, 2011';
        $this->assertTrue(Validator::date($check, compact('format')));

        $check = 'Oct 20 2011';
        $this->assertTrue(Validator::date($check, compact('format')));

        $check = 'Octob 20 2011';
        $this->assertFalse(Validator::date($check, compact('format')));


        $format = 'My';
        $check = 'October 2011';
        $this->assertTrue(Validator::date($check, compact('format')));

        $check = 'Oct 2011';
        $this->assertTrue(Validator::date($check, compact('format')));

        $check = 'Octob 2011';
        $this->assertFalse(Validator::date($check, compact('format')));


        $format = 'my';
        $check = '10 2011';
        $this->assertTrue(Validator::date($check, compact('format')));

        $check = '10.2011';
        $this->assertTrue(Validator::date($check, compact('format')));

        $check = '10-2011';
        $this->assertTrue(Validator::date($check, compact('format')));

        $check = '10/2011';
        $this->assertTrue(Validator::date($check, compact('format')));

        $check = '13/2011';
        $this->assertFalse(Validator::date($check, compact('format')));


        $format = 'dmy hms';
        $check = '20.10.2011 12:21:13';
        $this->assertTrue(Validator::date($check, compact('format')));

        $check = '20-10-2011 12:21:13';
        $this->assertTrue(Validator::date($check, compact('format')));

        $check = '20/10/2011 12:21:13';
        $this->assertTrue(Validator::date($check, compact('format')));

        $check = '20.10.2011 12:21';
        $this->assertTrue(Validator::date($check, compact('format')));

        $check = '20-10-2011 12:21';
        $this->assertTrue(Validator::date($check, compact('format')));

        $check = '20/10/2011 12:21';
        $this->assertTrue(Validator::date($check, compact('format')));

        $check = '20/10/2011 12';
        $this->assertFalse(Validator::date($check, compact('format')));

        $check = '20/10/2011 32:21';
        $this->assertFalse(Validator::date($check, compact('format')));

        $check = '29/02/2011 12:21:13';
        $this->assertFalse(Validator::date($check, compact('format')));
    }

    public function test_Decimal_ReturnsBool() {
        $check = '234';
        $this->assertTrue(Validator::decimal($check));

        $check = '+234.01';
        $this->assertTrue(Validator::decimal($check));

        $check = '-123.04e3';
        $this->assertTrue(Validator::decimal($check));

        $check = '234';
        $options = array('precision' => 2);
        $this->assertFalse(Validator::decimal($check, $options));

        $check = '+234.12';
        $options = array('precision' => 2);
        $this->assertTrue(Validator::decimal($check, $options));

        $check = '.23412';
        $options = array('precision' => 5);
        $this->assertTrue(Validator::decimal($check, $options));

        $check = '-.023412';
        $options = array('precision' => 5);
        $this->assertFalse(Validator::decimal($check, $options));

        $check = '2$34gdd';
        $this->assertFalse(Validator::decimal($check));
    }

    public function test_Email_ReturnsBool() {
		$this->assertTrue(Validator::email('abc.def@test.com'));
		$this->assertTrue(Validator::email('def@test.com'));
		$this->assertTrue(Validator::email('abc-def@test.com'));
		$this->assertTrue(Validator::email('abc_def@test.com'));
		$this->assertTrue(Validator::email('abc@def.gh.ij'));
		$this->assertTrue(Validator::email('abc-def@test-hyphen.net'));
		$this->assertTrue(Validator::email("a.b'cdef@test.com"));
		$this->assertTrue(Validator::email('abc+def@test.com'));
		$this->assertTrue(Validator::email('abc&def@test.com'));
		$this->assertTrue(Validator::email('abc.def@12345.com'));
		$this->assertTrue(Validator::email('abc.def@12345.co.uk'));
		$this->assertTrue(Validator::email('abc@g.cn'));
		$this->assertTrue(Validator::email('abc@x.com'));

		$this->assertTrue(Validator::email('abc@example.aero'));
		$this->assertTrue(Validator::email('abc@example.asia'));
		$this->assertTrue(Validator::email('abc@example.biz'));
		$this->assertTrue(Validator::email('abc@example.cat'));
		$this->assertTrue(Validator::email('abc@example.com'));
		$this->assertTrue(Validator::email('abc@example.coop'));
		$this->assertTrue(Validator::email('abc@example.edu'));
		$this->assertTrue(Validator::email('abc@example.gov'));
		$this->assertTrue(Validator::email('abc@example.info'));
		$this->assertTrue(Validator::email('abc@example.int'));
		$this->assertTrue(Validator::email('abc@example.jobs'));
		$this->assertTrue(Validator::email('abc@example.mil'));
		$this->assertTrue(Validator::email('abc@example.mobi'));
		$this->assertTrue(Validator::email('abc@example.museum'));
		$this->assertTrue(Validator::email('abc@example.name'));
		$this->assertTrue(Validator::email('abc@example.net'));
		$this->assertTrue(Validator::email('abc@example.org'));
		$this->assertTrue(Validator::email('abc@example.pro'));
		$this->assertTrue(Validator::email('abc@example.tel'));
		$this->assertTrue(Validator::email('abc@example.travel'));

		$this->assertTrue(Validator::email('_abc@example.com'));
		$this->assertTrue(Validator::email('abc@example.c'));
		$this->assertTrue(Validator::email('abc@example.com.a'));
		$this->assertTrue(Validator::email('abc@example.toolong'));

		$this->assertFalse(Validator::email('abc@example'));
		$this->assertFalse(Validator::email('abc.@example.com'));

		$this->assertFalse(Validator::email('abc@example.com.'));
		$this->assertFalse(Validator::email('abc@example..com'));
		$this->assertFalse(Validator::email('abc;@example.com'));
		$this->assertFalse(Validator::email('abc@example.com;'));
		$this->assertFalse(Validator::email('abc@efg@example.com'));
		$this->assertFalse(Validator::email('abc@@example.com'));
		$this->assertFalse(Validator::email('abc efg@example.com'));
		$this->assertFalse(Validator::email('abc,efg@example.com'));
		$this->assertFalse(Validator::email('abc@sub,example.com'));
		$this->assertFalse(Validator::email("abc@sub'example.com"));
		$this->assertFalse(Validator::email('abc@sub/example.com'));
		$this->assertFalse(Validator::email('abc@yahoo!.com'));
		$this->assertFalse(Validator::email("touché.surname@example.com"));
		$this->assertFalse(Validator::email('abc@example_underscored.com'));
		$this->assertFalse(Validator::email('too@many.dots.here....com'));
    }

    public function test_Ip_ReturnsBool() {
		$this->assertTrue(Validator::ip('127.0.0.1'));
		$this->assertTrue(Validator::ip('192.168.1.1'));

		$this->assertFalse(Validator::ip('42'));
		$this->assertFalse(Validator::ip('green'));
    }

    public function test_LengthBetween_ReturnsBool() {
        $check = '23412';
        $options = array('min' => 4, 'max' => 6);
        $this->assertTrue(Validator::length_between($check, $options));

        $check = 'abc';
        $options = array('min' => 2, 'max' => 255);
        $this->assertTrue(Validator::length_between($check, $options));

        $check = 'superstring';
        $options = array('min' => 1, 'max' => 3);
        $this->assertFalse(Validator::length_between($check, $options));

        $check = '';
        $this->assertFalse(Validator::length_between($check));
    }

    public function test_NotEmpty_ReturnsBool() {
        $this->assertTrue(Validator::not_empty('test'));
        $this->assertFalse(Validator::not_empty(''));
    }

    public function test_Numeric_ReturnsBool() {
        $this->assertTrue(Validator::numeric('-204'));
        $this->assertTrue(Validator::numeric(+403));
        $this->assertFalse(Validator::numeric('-'));
        $this->assertFalse(Validator::numeric('zero'));
    }

    public function test_WithinBounds_ReturnsBool() {
        $options = array(
            'lower' => 1,
            'upper' => 10
        );

		$check = 5;
		$this->assertTrue(Validator::within_bounds($check, $options));

		$check = 0;
		$this->assertFalse(Validator::within_bounds($check, $options));

		$check = 11;
		$this->assertFalse(Validator::within_bounds($check, $options));

        $options = array(
            'upper' => 1
        );
		$check = 0;
		$this->assertTrue(Validator::within_bounds($check, $options));

		$check = 2;
		$this->assertFalse(Validator::within_bounds($check, $options));

        $options = array(
            'lower' => 1
        );
		$check = 2;
		$this->assertTrue(Validator::within_bounds($check, $options));

		$check = 1;
		$this->assertFalse(Validator::within_bounds($check, $options));

        $check = 0;
        $options = array();
		$this->assertTrue(Validator::within_bounds($check, $options));
    }

    public function test_ZipCode_ReturnsBool() {
		$this->assertTrue(Validator::zip_code('97201'));
		$this->assertTrue(Validator::zip_code('97201-4707'));
		$this->assertTrue(Validator::zip_code('08232'));

		$this->assertFalse(Validator::zip_code('8232'));
		$this->assertFalse(Validator::zip_code('8232-'));
		$this->assertFalse(Validator::zip_code('82321-2'));
		$this->assertFalse(Validator::zip_code('82321-abc'));
		$this->assertFalse(Validator::zip_code('green'));
    }

}
?>
