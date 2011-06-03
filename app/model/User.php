<?php

namespace app\model;

class User extends \nx\core\Model {
    protected $id;

    protected $username;
    protected $password;
    protected $email;
    protected $ip;
    protected $join_date;
    protected $last_login;
    protected $template;

    protected $_sanitizers = array(
        'username'         => 's',
        'email'            => 's',
        'password'         => 's'
    );

    protected $_validators = array(
        'email' => array(
            array('email', 'message' => 'Email is invalid.')
        ),
        'username' => array(
            array('not_empty', 'message' => 'Username cannot be blank.'),
            array('alphanumeric', 'message' => 'Username must contain only alphanumeric characters.'),
            array('length_between', 'options' => array('min' => '5', 'max' => 16), 'message' => 'Username must be between 5 and 16 characters.'),
        ),
        'ip' => array(
            array('ip', 'message' => 'ip is invalid.')
        )
    );

    protected $_options = array(
        'username_min_length'    => 5,
        'username_max_length'    => 16,
        'password_min_length'    => 5,
        'password_max_length'    => 16,
        'password_special_chars' => '#@!$%._'
    );

}

?>
