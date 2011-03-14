<?php

class Tag extends ApplicationModel
{
    protected $id;

    protected $tag;

    protected $_has_and_belongs_to_many = array('Entry');
}

?>
