<?php

namespace nx\core;

use nx\core\View; 
use nx\lib\Auth; 
use nx\lib\Data; 
use nx\lib\File; 
use nx\lib\Meta; 
use nx\lib\Dispatcher; 

class Controller extends Object {

    protected $_http_get = array();
    protected $_http_post = array();

    protected $_template = DEFAULT_TEMPLATE; 

    protected $_token = null;

    protected $_sanitizers = array();

    public function __construct(array $config = array()) {
        $defaults = array(
            'http_get'  => $this->_http_get,
            'http_post' => $this->_http_post
        );
        parent::__construct($config + $defaults);
    }

    protected function _init() {
        parent::_init();

        $this->_http_get = $this->sanitize($this->_config['http_get']);
        if ( !$this->_is_valid_request($this->_http_get) ) {
            $this->handle_CSRF();
        }

        $this->_http_post = $this->sanitize($this->_config['http_post']);
        if ( !$this->_is_valid_request($this->_http_post) ) {
            $this->handle_CSRF();
        }

        $this->_token = Auth::create_token($this->classname());
    }

    public function call($action, $id = null) {
        if ( !method_exists($this, $action) || $this->is_protected($action) ) {
            return false;
        }   

        $to_view = $this->$action($id);

        if ( !$to_view ) {
            return false;
        }

        // AJAX
        if ( is_string($to_view) ) {
            echo $to_view;
            return true;
        }

        $view_file = ROOT_DIR . '/app/view/' . $this->_template . '/' . lcfirst($this->classname()) . '/' . $action . VIEW_EXTENSION;
        if ( !file_exists($view_file) ) {
            return false;
        }

        extract($to_view);
        include $view_file;
        return true;
    }

    public function get_template() {
        return $this->_template;
    }

    public function handle_CSRF() {
        // TODO: Handle CSRF more elegantly
        die('CSRF attack!');
    }

    public function is_protected($action) {
        return ( in_array($action, Meta::get_protected_methods($this)) );
    }

    protected function _is_valid_request($request) {
        if ( empty($request) ) {
            return true;
        }
        return Auth::is_token_valid($request, $this->classname());
    }

   /**
    *  Redirects the page.
    *       
    *  @param string $page      The page to be checked.
    *  @access public
    *  @return string
    */
    public function redirect($page) {
        if ( headers_sent() ) {
            echo '<meta content="0; url=' . $page . '" http-equiv="refresh"/>';
        } else {
            header('Location: ' . $page);
        }
        return false;
    }

    public function sanitize($data) {
        $sanitized = array();
        foreach ( $data as $key => $val ) {
            if ( !is_array($val) ) {
                if ( isset($this->_sanitizers[$key]) ) {
                    $sanitized[$key] = Data::sanitize($val, $this->_sanitizers[$key]);
                }
            } else {
                foreach ( $val as $id => $obj ) {
                    $sanitized[$key][$id] = $obj->sanitize();
                }
            }
        }
        return $sanitized;
    }

}

?>
