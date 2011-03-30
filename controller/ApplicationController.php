<?php

class ApplicationController extends core\Controller
{
    protected $_session;
    protected $_user;

    public function __construct($get=null, $post=null) 
    {
        parent::__construct($get, $post);

        $this->_session = $this->_get_default_session();

        if ( $this->_session->is_logged_in() )
        {
            $this->_user = $this->_get_default_user($this->_session->get_user_id());
            $this->_template = $this->_user->get_template();
        }
        else
        {
            $this->_user = null;
            $this->_template = DEFAULT_TEMPLATE;
        }
    }

    private function _get_default_session()
    {
        return new Session(); 
    }

    private function _get_default_user($user_id)
    {
        return new User($user_id);
    }
}

?>
