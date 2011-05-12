<?php

namespace nx\test\lib;

use nx\lib\Dispatcher;

class DispatcherTest extends \PHPUnit_Framework_TestCase {    

    public function test_ParseQueryString_ReturnsArray() {
        $query_string = 'controller=register';
        $args = Dispatcher::parse_query_string($query_string);
        $check = array(
            'controller' => 'Register',
            'action'     => DEFAULT_ACTION,
            'id'         => null, 
            'get'        => array()
        );
        $this->assertEquals($args, $check, 'Parsing a query string with the controller set (`' . $query_string . '`) failed to return the expected data format.');

        $query_string = 'controller=register&args=username=test';
        $args = Dispatcher::parse_query_string($query_string);
        $check = array(
            'controller' => 'Register',
            'action'     => DEFAULT_ACTION,
            'id'         => null, 
            'get'        => array('username' => 'test')
        );
        $this->assertEquals($args, $check, 'Parsing a query string with the controller and the args set (`' . $query_string . '`) failed to return the expected data format.');

        // TODO: Add the rest of the query strings
    }
}
?>
