<?php

namespace app\model;

class User extends ApplicationModel
{
    protected $id;

    protected $username;
    protected $password;
    protected $email;
    protected $ip;
    protected $join_date;
    protected $last_login;

    protected $_validators = array(
        'username' => array(
            array('not_empty', 'message' => 'Username cannot be blank.'),
            array('length_between', 'options' => array('min' => '5', 'max' => 16), 'message' => 'Username must be between 5 and 16 characters.'),
        ),
        'ip' => array(
            array('ip', 'message' => 'ip is invalid.')
        )
    );

    protected $_options = array(
        'username_min_length' => 5,
        'username_max_length' => 16,
        'password_min_length' => 5,
        'password_max_length' => 16
    );

    public function get_option($option) {
        return $this->_options[$option];
    }
}

?>
