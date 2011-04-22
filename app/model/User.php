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
        'username' => array('not_empty', 'message' => 'Username cannot be blank.'),
        'username' => array('length_between', 'options' => array('min' => '5', 'max' => 16), 'message' => 'Username must be between 5 and 16 characters.'),
        'ip' => array('ip', 'message' => 'ip is invalid.')
    );
}

?>
