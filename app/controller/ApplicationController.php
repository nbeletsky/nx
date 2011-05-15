<?php

namespace app\controller;

class ApplicationController extends \nx\core\Controller {

    protected $_session;
    protected $_user;
    protected $_form;

    protected $_classes = array(
        'session' => 'app\model\Session', 
        'user'    => 'app\model\User',
        'form'    => 'nx\lib\Form' 
    );

    public function __construct(array $config = array()) {
        $defaults = array(
            'classes' => $this->_classes
        );
        parent::__construct($config + $defaults);
    }

    protected function _init() {
        $session = $this->_config['classes']['session'];
        $this->_session = new $session(); 

        if ( $this->_session->is_logged_in() ) {
            $user = $this->_config['classes']['user'];
            $this->_user = new $user(array('id' => $this->_session->get_user_id()));
            $this->_template = $this->_user->get_template();
        } else {
            $this->_user = null;
            $this->_template = DEFAULT_TEMPLATE;
        }

        $form = $this->_config['classes']['form'];
        $this->_form = new $form();

        parent::_init();
    }

}

?>
