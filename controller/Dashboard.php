<?php

class Dashboard extends ApplicationController
{
    public function index()
    {
        /*
        $where = array('username' => 'Derp2');
        $user = new User($where); 
        $entries = $user->Entry;
        $debug = new \lib\Debug();
        foreach ( $entries as $entry )
        {
            $debug->inspect(1, $entry);
        }
            die();
            */
        
        $entry = new Tag(2); 
        $tags = $entry->Entry;
        $debug = new \lib\Debug();
        $debug->inspect(1, $tags);
        die();
        return array("contact_email"=>"test@test.com");
    }   

    public function login()
    {
        if ( $this->_user )
        {
            $this->redirect('index');
        }

        if ( count($this->_http_get) )    
        {
            $username = $this->_http_get['username'];
            $where = array('username' => $username);
            $user = new User($where); 
            $encrypt = new Encrypt();

            if ( $this->_session->login($username, $this->_http_get['password'], $_SERVER['REMOTE_ADDR'], $user, $encrypt) )
            {
                $this->redirect('index');
            }
            else
            {
                return array('error_msg' => 'Invalid username/password combination.');
            }
        }

        // Display the login page
    }
    
}
