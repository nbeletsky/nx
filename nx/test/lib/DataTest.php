<?php

namespace nx\test\lib;

use nx\lib\Data;

class DataTest extends \PHPUnit_Framework_TestCase {    

    // TODO: Flesh this out more!
    public function test_Sanitizers_ReturnCleanData() {
        $dirty = '<html><body>';
        $check = '&#60;html&#62;&#60;body&#62;';
        $type = 's';
        $clean = Data::sanitize($dirty, $type);
        $this->assertEquals($clean, $check);
    }
}
?>
