<?php

/**
 * NX
 *
 * @author    Nick Sinopoli <NSinopoli@gmail.com>
 * @copyright Copyright (c) 2011, Nick Sinopoli
 * @license   http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace nx\core;

use nx\lib\Meta;

/*
 *  The `Object` class is the class from which the other
 *  core classes inherit.  Rather than rely on complex
 *  method signatures for class instantiation, it provides
 *  a simple mechanism by which configuration settings
 *  can be passed to class constructors by means of an
 *  array, and optionally 'initializes' an object following
 *  construction.
 *
 *  @package core
 */
class Object {

   /**
    *  The configuration settings.
    *
    *  @var array
    *  @access protected
    */
    protected $_config = array();

   /**
    *  The object's classname.
    *
    *  @var string
    *  @access protected
    */
    protected $_classname;

   /**
    *  Loads the configuration settings for the class.
    *
    *  @param array $config        The configuration options.
    *  @access public
    *  @return void
    */
    public function __construct(array $config = array()) {
        $defaults = array('init' => true);
        $this->_config = $config + $defaults;

        if ( $this->_config['init'] ) {
            $this->_init();
        }
    }

   /**
    *  Initializes the class.
    *
    *  @access public
    *  @return void
    */
    protected function _init() {
        $this->_classname = Meta::classname_only(get_called_class());
    }

   /**
    *  Returns the object's classname without the namespace.
    *
    *  @access public
    *  @return string
    */
    public function classname() {
        return $this->_classname;
    }

}

?>
