<?php

namespace lib;

class Validator extends Object {

    public function __construct(array $config = array()) {
        $defaults = array();
        parent::__construct($config + $defaults);
    }

}

?>
