<?php

class Entry extends ApplicationModel
{
    protected $id;

    protected $title;
    protected $content;
    protected $created_on;
    protected $User_id;

    protected $_belongs_to = array('User');
    protected $_has_and_belongs_to_many = array('Tag');

}

?>
