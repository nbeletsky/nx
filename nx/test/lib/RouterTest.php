<?php

namespace nx\test\lib;

use nx\lib\Router;

class RouterTest extends \PHPUnit_Framework_TestCase {

    public function test_ParseUrl_ReturnsArray() {
        $query_string = '';
        $args = Router::parse_url($query_string);
        $check = array(
            'controller' => Router::$defaults['controller'],
            'action'     => Router::$defaults['action'],
            'id'         => Router::$defaults['id'],
            'get'        => array()
        );
        $this->assertEquals($args, $check, 'Parsing an empty query string (`'
            . $query_string . '`) failed to return the expected data format.');

        $query_string = '/';
        $args = Router::parse_url($query_string);
        $check = array(
            'controller' => Router::$defaults['controller'],
            'action'     => Router::$defaults['action'],
            'id'         => Router::$defaults['id'],
            'get'        => array()
        );
        $this->assertEquals($args, $check, 'Parsing a query string of (`'
            . $query_string . '`) failed to return the expected data format.');

        $query_string = '/register';
        $args = Router::parse_url($query_string);
        $check = array(
            'controller' => 'Register',
            'action'     => Router::$defaults['action'],
            'id'         => Router::$defaults['id'],
            'get'        => array()
        );
        $this->assertEquals($args, $check, 'Parsing a query string with the controller set (`'
            . $query_string . '`) failed to return the expected data format.');

        $query_string = '/register/42';
        $args = Router::parse_url($query_string);
        $check = array(
            'controller' => 'Register',
            'action'     => Router::$defaults['action'],
            'id'         => '42',
            'get'        => array()
        );
        $this->assertEquals($args, $check, 'Parsing a query string with the'
            . ' controller and id set (`' . $query_string . '`) failed to return'
            . ' the expected data format.');

        $query_string = '/register?username=test';
        $args = Router::parse_url($query_string);
        $check = array(
            'controller' => 'Register',
            'action'     => Router::$defaults['action'],
            'id'         => Router::$defaults['id'],
            'get'        => array('username' => 'test')
        );
        $this->assertEquals($args, $check, 'Parsing a query string with the'
            . ' controller and one arg set (`' . $query_string . '`) failed to return'
            . ' the expected data format.');

        $query_string = '/register?username=test&token=42';
        $args = Router::parse_url($query_string);
        $check = array(
            'controller' => 'Register',
            'action'     => Router::$defaults['action'],
            'id'         => Router::$defaults['id'],
            'get'        => array(
                'username' => 'test',
                'token'    => '42'
            )
        );
        $this->assertEquals($args, $check, 'Parsing a query string with the'
            . ' controller and multiple args set (`' . $query_string . '`) failed to'
            . ' return the expected data format.');

        $query_string = '/register/index';
        $args = Router::parse_url($query_string);
        $check = array(
            'controller' => 'Register',
            'action'     => 'index',
            'id'         => Router::$defaults['id'],
            'get'        => array()
        );
        $this->assertEquals($args, $check, 'Parsing a query string with the'
            . ' controller and action set (`' . $query_string . '`) failed to return'
            . ' the expected data format.');

        $query_string = '/register/42?username=test&token=42';
        $args = Router::parse_url($query_string);
        $check = array(
            'controller' => 'Register',
            'action'     => Router::$defaults['action'],
            'id'         => '42',
            'get'        => array(
                'username' => 'test',
                'token'    => '42'
            )
        );
        $this->assertEquals($args, $check, 'Parsing a query string with the'
            . ' controller, id, and multiple args set (`' . $query_string . '`)'
            . ' failed to return the expected data format.');

        $query_string = '/register/index/42';
        $args = Router::parse_url($query_string);
        $check = array(
            'controller' => 'Register',
            'action'     => 'index',
            'id'         => '42',
            'get'        => array()
        );
        $this->assertEquals($args, $check, 'Parsing a query string with the'
            . ' controller, action, and id set (`' . $query_string . '`) failed to'
            . ' return the expected data format.');

        $query_string = '/register/index?username=test&token=42';
        $args = Router::parse_url($query_string);
        $check = array(
            'controller' => 'Register',
            'action'     => 'index',
            'id'         => Router::$defaults['id'],
            'get'        => array(
                'username' => 'test',
                'token'    => '42'
            )
        );
        $this->assertEquals($args, $check, 'Parsing a query string with the'
            . ' controller, action, and multiple args set (`' . $query_string . '`)'
            . ' failed to return the expected data format.');

        $query_string = '/register/index/42?username=test&token=42';
        $args = Router::parse_url($query_string);
        $check = array(
            'controller' => 'Register',
            'action'     => 'index',
            'id'         => '42',
            'get'        => array(
                'username' => 'test',
                'token'    => '42'
            )
        );
        $this->assertEquals($args, $check, 'Parsing a query string with the'
            . ' controller, action, id, and multiple args set (`' . $query_string
            . '`) failed to return the expected data format.');
    }

}
?>
