<?php

namespace nx\core;

use nx\lib\Form; 

class View extends Object {

    protected $_form;

    public function __construct(array $config = array()) {
        $defaults = array();
        parent::__construct($config + $defaults);
    }

    protected function _init() {
        parent::_init();
        $this->_form = new Form();
    }

    public function output($file, $variables) {
        extract($variables);
        include $file;
    }
    
    
}

?>
