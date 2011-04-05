<?php

namespace core;

use lib\Data; 
use lib\File; 
use lib\Meta; 

class Controller extends Object {

    protected $_handler = null; // handler for errors, array('controller'=>foo, 'action'=>bar)

    protected $_http_get = array();
    protected $_http_post = array();

    protected $_validation_errors = array();

    protected $_template = DEFAULT_TEMPLATE; 
    protected $_create_snapshot = false; 

    protected $_token = null;

    public function __construct($get = null, $post = null) {
        $data = new Data();
        $this->_http_get = ( !is_null($get) ) ? $get : array();
        $this->_http_post = ( isset($post) ) ? $data->extract_post($post) : array();
    }

    public function call($action, $id = null, $additional = null) {
        try {
            
            if ( !method_exists($this, $action) || $this->is_protected($action) ) {
                // TODO: throw 404!
                die();
            }   

            $this->_validation_errors = $this->_validate($action);

            // TODO: Move this?
            $this->_token = sha1(microtime() . CSRF_TOKEN_SALT);
            $_SESSION[$this->classname() . '_token'] = $this->_token;

            // TODO: Fix preload?  Eliminate it?  Find some other way of preloading?
            $this->preload($action);
            
            $to_view = $this->$action($id);

            // AJAX
            if ( is_string($to_view) ) {
                echo $to_view;
                exit;
            }
            
            $view_file = "../view/" . $this->_template . '/' . get_class($this) . "/" . $action . VIEW_EXTENSION;

            if ( file_exists($view_file) ) {
                if ( is_array($additional) ) {
                    extract($additional);
                }

                if ( is_array($to_view) ) {
                    extract($to_view);
                }

                include($view_file);

                if ( $this->_create_snapshot ) {
                    $snapshot = ob_get_contents(); 
                    $file = new File(); 
                    $file->create_snapshot($snapshot, basename(realpath($view_file)));
                }
            }

        } catch (\Exception $e) {
            // TODO: Fix exceptions
            if ( $this->_handler ) {
                render("/" . $this->_handler['controller'] . "/" . $this->_handler['action'] . "/" .$this->id);
            }
            
            print_r("<pre>" . $e->getMessage() . $e->getTraceAsString() . '</pre>');
            exit();
        }  

    }

    public function is_protected($action) {
        $meta = new Meta();
        return ( in_array($action, $meta->get_protected_methods($this)) );
    }

    public function preload($action) {
        // preload some stuff
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
        return array();
    }
    
}

?>
