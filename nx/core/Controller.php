<?php

/**
 * NX
 *
 * @author    Nick Sinopoli <NSinopoli@gmail.com>
 * @copyright Copyright (c) 2011, Nick Sinopoli
 * @license   http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace nx\core;

use nx\lib\Auth;
use nx\lib\Data;
use nx\lib\Meta;

/*
 *  The `Controller` class is the parent class of all
 *  application controllers.  It provides access to sanitized
 *  $_POST and $_GET data and ensures protection against CSRF
 *  attacks.
 *
 *  @package core
 */
class Controller extends Object {

   /**
    *  Whether or not the controller should be
    *  publicly accessible.
    *
    *  @var bool
    *  @access protected
    */
    protected $_accessible = true;

   /**
    *  The sanitized data from $_GET.
    *
    *  @var array
    *  @access protected
    */
    protected $_http_get = array();

   /**
    *  The sanitized data from $_POST.
    *
    *  @var array
    *  @access protected
    */
    protected $_http_post = array();

   /**
    *  The sanitizers to be used when parsing
    *  request data.  Acceptable sanitizers are:
    *  `key` => `b` for booleans
    *  `key` => `f` for float/decimals
    *  `key` => `i` for integers
    *  `key` => `s` for strings
    *
    *  @see /nx/lib/Data::sanitize()
    *  @see /nx/core/Controller->sanitize()
    *  @var array
    *  @access protected
    */
    protected $_sanitizers = array();

   /**
    *  The session object.
    *
    *  @var object
    *  @access protected
    */
    protected $_session;

   /**
    *  The controller template.
    *
    *  @var string
    *  @access protected
    */
    protected $_template = 'web';

   /**
    *  The request token.
    *
    *  @var string
    *  @access protected
    */
    protected $_token = null;

   /**
    *  The user object.
    *
    *  @var object
    *  @access protected
    */
    protected $_user;

   /**
    *  Loads the configuration settings for the controller.
    *
    *  @param array $config        The configuration options.
    *  @access public
    *  @return void
    */
    public function __construct(array $config = array()) {
        $defaults = array(
            'classes'   => array(
                'session' => 'app\model\Session',
                'user'    => 'app\model\User'
            ),
            'http_get'  => $this->_http_get,
            'http_post' => $this->_http_post,
            'view_dir'  => dirname(dirname(__DIR__)) . '/app/view/'
        );
        parent::__construct($config + $defaults);
    }

   /**
    *  Initializes the controller with sanitized http request data,
    *  generates a token to be used to ensure that the next request is valid,
    *  and loads a user object if a valid session is found.
    *
    *  @access protected
    *  @return void
    */
    protected function _init() {
        parent::_init();

        $session = $this->_config['classes']['session'];
        $this->_session = new $session();

        $this->_http_get = $this->sanitize($this->_config['http_get']);
        $this->_http_post = $this->sanitize($this->_config['http_post']);

        if ( !$this->_is_valid_request($this->_http_get)
            || !$this->_is_valid_request($this->_http_post) ) {
            $this->handle_CSRF();
            $this->_token = null;
        }

        if ( is_null($this->_token) ) {
            $this->_token = Auth::create_token();
        }

        if ( $this->_session->is_logged_in() ) {
            $user = $this->_config['classes']['user'];
            $this->_user = new $user(array('id' => $this->_session->get_user_id()));
            $this->_template = $this->_user->get_template();
        }
    }

   /**
    *  Calls the controller method, whose return values can then
    *  be passed to and parsed by a view.
    *
    *  @param string $method       The method.
    *  @param int $id              The id (passed from the URL, useful with
    *                              query strings like `http://foobar.com/entry/23`
    *                              or `http://foobar.com/entry/view/23`).
    *  @access public
    *  @return mixed
    */
    public function call($method, $id = null) {
        if ( !method_exists($this, $method) || $this->is_protected($method) ) {
            return false;
        }

        $rest_results = array();
        if ( !empty($this->_http_get) && method_exists($this, '_get') ) {
            $rest_results['get'] = $this->_get($id);
        }

        if ( !empty($this->_http_post) && method_exists($this, '_post') ) {
            $rest_results['post'] = $this->_post($id);
        }

        $results = $this->$method($id, $rest_results);

        if ( is_null($results) || $results === false ) {
            return false;
        }

        $to_view = array(
            'file' => $this->_config['view_dir'] . $this->_template . '/'
                . lcfirst($this->classname()) . '/' . $method . '.html',
            'vars' => $results
        );

        return $to_view;
    }

   /**
    *  Returns the current template.
    *
    *  @access public
    *  @return string
    */
    public function get_template() {
        return $this->_template;
    }

   /**
    *  Handles CSRF attacks.
    *
    *  @access public
    *  @return void
    */
    public function handle_CSRF() {
        // TODO: Log this as a potential CSRF attack
        die('CSRF attack!');
    }

   /**
    *  Returns whether or not the controller is
    *  publicly accessible.
    *
    *  @access public
    *  @return bool
    */
    public function is_accessible() {
        return $this->_accessible;
    }

   /**
    *  Checks if a method is protected.
    *
    *  @param string $method       The method.
    *  @access public
    *  @return bool
    */
    public function is_protected($method) {
        return ( in_array($method, Meta::get_protected_methods($this)) );
    }

   /**
    *  Checks that the token submitted with the
    *  request data is valid.
    *
    *  @param array $request       The request data.
    *  @access protected
    *  @return bool
    */
    protected function _is_valid_request($request) {
        if ( empty($request) ) {
            return true;
        }
        return Auth::is_token_valid($request, $this->classname());
    }

   /**
    *  Redirects the page.
    *
    *  @param string $page         The page to be redirected to.
    *  @access public
    *  @return bool
    */
    public function redirect($page) {
        if ( headers_sent() ) {
            echo '<meta content="0; url=' . $page . '" http-equiv="refresh"/>';
        } else {
            header('Location: ' . $page);
        }
        return false;
    }

   /**
    *  Sanitizes data according to the sanitizers defined in $this->_sanitizers.
    *  If data is an object, the object's sanitize() method will be called.
    *
    *  @param array $data          The data to be sanitized.
    *  @access public
    *  @return array
    */
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
