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
            array('notEmpty', 'message' => 'email is empty'),
            array('email', 'message' => 'email is not valid'),
        ),
        'ip' => array('ip', 'message' => 'ip is invalid')
    );
}

?>
