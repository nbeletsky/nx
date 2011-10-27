<?php

namespace nx\test\plugin\mail;

use nx\plugin\mail\SMTP;

class SMTPTest extends \PHPUnit_Framework_TestCase {

    protected $_smtp;

    public function setUp() {
        $config = array(
            'host' => 'ssl://smtp.gmail.com',
            'port' => 465,
            'username' => '',
            'password' => ''
        );
        $this->_smtp = new SMTP($config);
    }

    public function test_SendMail_ReturnsTrue() {
        $options = array(
            'from'    => '',
            'to'      => '',
            'subject' => 'Test Mail',
            'message' => 'Red, orange, green, yellow'
        );
        $this->assertTrue($this->_smtp->send($options), 'Mail was not sent properly!');
    }

}
?>
