<?php

namespace core;

use lib\Meta;

class Object {

    protected $_config = array();
    protected $_auto_config = array();

    protected $_classname;

    public function __construct(array $config = array()) {
        $defaults = array('init' => true);
        $this->_config = $config + $defaults;

        if ( $this->_config['init'] ) {
            $this->_init();
        }
    }

    protected function _init() {
        foreach ( $this->_auto_config as $key => $flag ) {
            if ( !isset($this->_config[$key] ) && !isset($this->_config[$flag]) ) {
                continue;
            }

            if ( $flag === 'merge' ) {
                $property = '_' . $key;
                $this->$property = $this->_config[$key] + $this->$property;
            } else {
                $property = '_' . $flag;
                $this->$property = $this->_config[$flag];
            }
        }

        $this->_classname = Meta::classname_only(get_called_class());
    }

    public function classname() {
        return $this->_classname;
    }

}

?>
