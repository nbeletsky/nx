<?php

namespace nx\core;

use nx\core\View; 
use nx\lib\Data; 
use nx\lib\File; 
use nx\lib\Meta; 
use nx\lib\Page; 

class Controller extends Object {

    protected $_http_get = array();
    protected $_http_post = array();

    protected $_template = DEFAULT_TEMPLATE; 

    protected $_token = null;

    protected $_sanitizers = array();
    protected $_validators = array();

    protected $_auto_config = array('http_get', 'http_post');

    public function __construct(array $config = array()) {
        $defaults = array();
        parent::__construct($config + $defaults);
    }

    protected function _init() {
        parent::_init();
        $this->_http_post = $this->sanitize($this->_http_post);
        $this->_http_get = $this->sanitize($this->_http_get);
    }

    public function call($action, $id = null, $additional = array()) {
        if ( !method_exists($this, $action) || $this->is_protected($action) ) {
            Page::throw_404($this->_template);
            exit;
        }   

        // TODO: Move this into an auth class?
        $this->_validate($action);

        $to_view = $this->$action($id);

        // AJAX
        if ( is_string($to_view) ) {
            echo $to_view;
            exit;
        }
        
        $view_file = "../view/" . $this->_template . '/' . lcfirst($this->classname()) . "/" . $action . VIEW_EXTENSION;

        if ( file_exists($view_file) ) {
            $view = new View();
            $view->output($view_file, $to_view + $additional); 
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
        exit;
    }

    public function sanitize($data) {
        foreach ( $data as $key => $val ) {
            if ( !is_array($key) ) {
                $data[$key] = Data::sanitize($val, $this->_sanitizers[$key]);
            } else {
                foreach ( $key as $id => $obj ) {
                    $data[$key][$id] = $obj->sanitize();
                }
            }
        }
        return $data;
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
