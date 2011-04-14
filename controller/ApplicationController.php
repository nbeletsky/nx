<?php

namespace controller;

class ApplicationController extends \core\Controller {

    protected $_session;
    protected $_user;

    // TODO: Fix this class so that these classes are set in the default() and then called via a config
    protected $_classes = array(
        'session' => 'model\Session', 
        'user'    => 'model\User'
    );

    public function __construct(array $config = array()) {
        $defaults = array();
        parent::__construct($config + $defaults);

        $session = $this->_classes['session'];
        $this->_session = new $session(); 

        if ( $this->_session->is_logged_in() ) {
            $user = $this->_classes['user'];
            $this->_user = new $user($this->_session->get_user_id());
            $this->_template = $this->_user->get_template();
        } else {
            $this->_user = null;
            $this->_template = DEFAULT_TEMPLATE;
        }
    }

}

?>
