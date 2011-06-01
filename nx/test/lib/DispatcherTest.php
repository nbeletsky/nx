<?php

namespace nx\test\lib;

use nx\lib\Dispatcher;

class DispatcherTest extends \PHPUnit_Framework_TestCase {    

    public function test_ParseQueryString_ReturnsArray() {
        $query_string = '';
        $args = Dispatcher::parse_query_string($query_string);
        $check = array(
            'controller' => 'Dashboard',
            'action'     => 'index',
            'id'         => null, 
            'get'        => array()
        );
        $this->assertEquals($args, $check, 'Parsing an empty query string (`' . $query_string . '`) failed to return the expected data format.');

        $query_string = 'controller=register';
        $args = Dispatcher::parse_query_string($query_string);
        $check = array(
            'controller' => 'Register',
            'action'     => 'index',
            'id'         => null, 
            'get'        => array()
        );
        $this->assertEquals($args, $check, 'Parsing a query string with the controller set (`' . $query_string . '`) failed to return the expected data format.');

        $query_string = 'controller=register&id=42';
        $args = Dispatcher::parse_query_string($query_string);
        $check = array(
            'controller' => 'Register',
            'action'     => 'index',
            'id'         => '42', 
            'get'        => array()
        );
        $this->assertEquals($args, $check, 'Parsing a query string with the controller and id set (`' . $query_string . '`) failed to return the expected data format.');

        $query_string = 'controller=register&args=username=test';
        $args = Dispatcher::parse_query_string($query_string);
        $check = array(
            'controller' => 'Register',
            'action'     => 'index',
            'id'         => null, 
            'get'        => array('username' => 'test')
        );
        $this->assertEquals($args, $check, 'Parsing a query string with the controller and one arg set (`' . $query_string . '`) failed to return the expected data format.');

        $query_string = 'controller=register&args=username=test&token=42';
        $args = Dispatcher::parse_query_string($query_string);
        $check = array(
            'controller' => 'Register',
            'action'     => 'index',
            'id'         => null, 
            'get'        => array(
                'username' => 'test',
                'token'    => '42'
            )
        );
        $this->assertEquals($args, $check, 'Parsing a query string with the controller and multiple args set (`' . $query_string . '`) failed to return the expected data format.');

        $query_string = 'controller=register&action=index';
        $args = Dispatcher::parse_query_string($query_string);
        $check = array(
            'controller' => 'Register',
            'action'     => 'index',
            'id'         => null, 
            'get'        => array()
        );
        $this->assertEquals($args, $check, 'Parsing a query string with the controller and action set (`' . $query_string . '`) failed to return the expected data format.');

        $query_string = 'controller=register&id=42&args=username=test&token=42';
        $args = Dispatcher::parse_query_string($query_string);
        $check = array(
            'controller' => 'Register',
            'action'     => 'index',
            'id'         => '42', 
            'get'        => array(
                'username' => 'test',
                'token'    => '42'
            )
        );
        $this->assertEquals($args, $check, 'Parsing a query string with the controller, id, and multiple args set (`' . $query_string . '`) failed to return the expected data format.');

        $query_string = 'controller=register&action=index&id=42';
        $args = Dispatcher::parse_query_string($query_string);
        $check = array(
            'controller' => 'Register',
            'action'     => 'index',
            'id'         => '42', 
            'get'        => array()
        );
        $this->assertEquals($args, $check, 'Parsing a query string with the controller, action, and id set (`' . $query_string . '`) failed to return the expected data format.');

        $query_string = 'controller=register&action=index&args=username=test&token=42';
        $args = Dispatcher::parse_query_string($query_string);
        $check = array(
            'controller' => 'Register',
            'action'     => 'index',
            'id'         => null, 
            'get'        => array(
                'username' => 'test',
                'token'    => '42'
            )
        );
        $this->assertEquals($args, $check, 'Parsing a query string with the controller, action, and multiple args set (`' . $query_string . '`) failed to return the expected data format.');

        $query_string = 'controller=register&action=index&id=42&args=username=test&token=42';
        $args = Dispatcher::parse_query_string($query_string);
        $check = array(
            'controller' => 'Register',
            'action'     => 'index',
            'id'         => '42', 
            'get'        => array(
                'username' => 'test',
                'token'    => '42'
            )
        );
        $this->assertEquals($args, $check, 'Parsing a query string with the controller, action, id, and multiple args set (`' . $query_string . '`) failed to return the expected data format.');
    }
}
?>
