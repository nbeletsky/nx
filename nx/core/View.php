<?php

/**
 * NX
 *
 * @author    Nick Sinopoli <NSinopoli@gmail.com>
 * @copyright Copyright (c) 2011, Nick Sinopoli
 * @license   http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace nx\core;

class View extends Object {

    protected $_form;

    protected $_classes = array(
        'form'    => 'nx\lib\Form' 
    );

   /**
    *  Loads the configuration settings for the view.
    *  
    *  @access public
    *  @return void
    */
    public function __construct(array $config = array()) {
        $defaults = array(
            'classes'   => $this->_classes
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

    public function render($file, $vars) {
        // AJAX
        if ( is_string($vars) ) {
            echo htmlspecialchars($vars, ENT_QUOTES, 'UTF-8');
            return true;
        }

        // TODO: Axe this constant, and throw a 404!
        $file = NX_ROOT . '/app/view/' . $file;
        if ( !file_exists($file) ) {
            return false;
        }

        extract($vars);
        include $file;

        return true;
    }

}

?>
