<?php

class User extends ApplicationModel
{
    protected $id;

    protected $username;
    protected $password;
    protected $ip;
    protected $join_date;
    protected $last_login;

    protected $_has_many = array('Entry');
}

?>
