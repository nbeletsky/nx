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
    *  @param array $config         The configuration options.
    *  @access public
    *  @return void
    */
    public function __construct(array $config = array()) {
        $defaults = array(
            'classes'   => array(
                'compiler' => 'nx\lib\Compiler',
                'form'     => 'nx\lib\Form'
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
    *  @param string $file          The file to be rendered.
    *  @param mixed $vars           Variables to be substituted in the view.
    *  @access public
    *  @return string
    */
    public function render($file, $vars) {
        if ( is_array($vars) ) {
            extract($vars);
        }

        $compiler = $this->_config['classes']['compiler'];
        $template = $compiler::compile($file);

        ob_start();
        require $template;
        return ob_get_clean();
    }

}

?>
