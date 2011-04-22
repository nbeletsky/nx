<?php

namespace nx\core;

use nx\lib\Form; 

class View extends Object {

    public function __construct(array $config = array()) {
        $defaults = array();
        parent::__construct($config + $defaults);
    }

    public function output($file, $variables) {
        extract($variables);
        include $file;
    }
    
    
}

?>
