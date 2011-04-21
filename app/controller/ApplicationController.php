<?php

namespace app\controller;

class ApplicationController extends \nx\core\Controller {

    protected $_session;
    protected $_user;

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
