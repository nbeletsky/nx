<?php

namespace core;

use lib\Data; 
use lib\File; 
use lib\Meta; 

class Controller extends Object {

    protected $_auto_config = array('http_get', 'http_post');

    protected $_http_get = array();
    protected $_http_post = array();

    protected $_validation_errors = array();

    protected $_template = DEFAULT_TEMPLATE; 

    protected $_token = null;

    public function __construct($config = array()) {
        $defaults = array();
        parent::__construct($config + $defaults);
    }

    public function call($action, $id = null, $additional = null) {
        if ( !method_exists($this, $action) || $this->is_protected($action) ) {
            // TODO: throw 404!
            die();
        }   

        // TODO: Move this into an auth class?
        $this->_validation_errors = $this->_validate($action);

        $to_view = $this->$action($id);

        // AJAX
        if ( is_string($to_view) ) {
            echo $to_view;
            exit;
        }
        
        $view_file = "../view/" . $this->_template . '/' . $this->classname() . "/" . $action . VIEW_EXTENSION;

        if ( file_exists($view_file) ) {
            if ( is_array($additional) ) {
                extract($additional);
            }

            if ( is_array($to_view) ) {
                extract($to_view);
            }

            include $view_file;
        }
    }

    public function is_protected($action) {
        return ( in_array($action, Meta::get_protected_methods($this)) );
    }

   /**
    *  Provides the redirect location based on the page provided.
    *       
    *  @param string $page      The page to be checked.
    *  @access public
    *  @return string
    */
    public function redirect($page) {
        $query = '?' . parse_url($page, PHP_URL_QUERY);
        $page = str_replace($query, '', $page);
        //$redirect_location = $_SERVER['SERVER_NAME'] . '/';
        switch ( $page ) {
            case 'index':
                $redirect_location = 'index.php';
                break;    
            default:
                //$redirect_location .= 'index.php';
                $redirect_location = 'index.php';
                break;    
        }

        if ( headers_sent() ) {
            echo '<meta content="0; url=' . $redirect_location . '" http-equiv="refresh"/>';
        } else {
            header("Location: $redirect_location");
        }
        exit();
    }

    // TODO: Fix this
    protected function _validate($action) {
        if ( !empty($this->_http_post) ) {
            if ( $this->_http_post['token'] !== $_SESSION[$this->classname() . '_token'] ) {
                // CSRF attack
                die('CSRF detected!');
            }
        }

        // TODO: Pass in CSRF_TOKEN_SALT as a config option?
        $this->_token = sha1(microtime() . CSRF_TOKEN_SALT);
        $_SESSION[$this->classname() . '_token'] = $this->_token;

        return array();
    }
    
}

?>
