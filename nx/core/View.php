<?php

/**
 * NX
 *
 * @author    Nick Sinopoli <NSinopoli@gmail.com>
 * @copyright Copyright (c) 2011, Nick Sinopoli
 * @license   http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace nx\core;

/*
 *  The `View` class is the parent class of all
 *  application views.  It provides access to a
 *  form helper for assistance with creating common
 *  page elements.
 *
 *  @package core
 */
class View extends Object {

   /**
    *  The form helper object.
    *
    *  @var object
    *  @access protected
    */
    protected $_form;

   /**
    *  Loads the configuration settings for the view.
    *
    *  @access public
    *  @return void
    */
    public function __construct(array $config = array()) {
        $defaults = array(
            'classes'   => array(
                'form' => 'nx\lib\Form'
            )
        );
        parent::__construct($config + $defaults);
    }

   /**
    *  Initializes a form for use within the view.
    *
    *  @access protected
    *  @return void
    */
    protected function _init() {
        parent::_init();

        $form = $this->_config['classes']['form'];
        $this->_form = new $form();
    }

   /**
    *  Renders a given file with the supplied variables.
    *
    *  @param string $file         The file to be rendered.
    *  @param mixed $vars          Variables to be substituted in the view.
    *  @access public
    *  @return bool
    */
    public function render($file, $vars) {
        if ( is_array($vars) ) {
            extract($vars);
        }

        require 'app/view/' . $file;
        return true;
    }

}

?>
