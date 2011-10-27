<?php

namespace nx\test\plugin\db;

use nx\plugin\db\PDO_MySQL;

class PDO_MySQLTest extends \PHPUnit_Framework_TestCase {

    protected $_db;

    public function setUp() {
        $this->_db = new PDO_MySQL();
    }
}
?>
