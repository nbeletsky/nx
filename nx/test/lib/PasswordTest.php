<?php

namespace nx\test\lib;

use nx\lib\Password;

class PasswordTest extends \PHPUnit_Framework_TestCase {    

    public function test_Hash_ReturnsUniqueValues() {
        $password = new Password();

        $correct = 'test1234';
        $hash = $password->get_hash($correct);
        $check = $password->check($correct, $hash);
        $this->assertTrue($check, 'Hashing a password and then checking it does not return true!');

        $wrong = 'wrong!';
        $check = $password->check($wrong, $hash);
        $this->assertFalse($check, 'Hashing a password and then checking it with an incorrect password does not return false!');
    }

}

?>
