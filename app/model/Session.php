<?php

namespace app\model;

use nx\lib\String;

class Session extends ApplicationModel {
    protected $id;

    protected $data = '';

   /**
    *  The timestamp of the user's last activity.
    *
    *  @var string $last_active
    *  @access protected
    */
    protected $last_active;

   /**
    *  The user's id.
    *
    *  @var int $User_id
    *  @access protected
    */
    protected $User_id = 0;

    const SESSION_LIFETIME    = 3600;            // 60 minutes
    const LOGIN_COOKIE_EXPIRE = 2592000;         // Cookie expiration date (30 days)
    const SESSION_SALT        = 'M^mc?(9%ZKx[';  // Session salt
    const COOKIE_ID_NAME      = 'nx_id';         // Name of the cookie for the user date

   /**
    *  Constructor.
    * 
    *  @access public
    *  @return void
    */
    public function __construct(array $config = array()) {
        $defaults = array();
        parent::__construct($config + $defaults);
    }

    protected function _init() {
        parent::_init();

        $this->last_active = date('Y-m-d H:i:s', time());

        session_set_save_handler(
            array($this, 'open'),
            array($this, 'close'),
            array($this, 'read'),
            array($this, 'write'),
            array($this, 'destroy'),
            array($this, 'gc')
        );

        session_start();
    }

   /**
    *  Executes when the session operation is done.
    * 
    *  @access public
    *  @return bool
    */
    public function close() {
        return true;
    }

   /**
    *  Creates a new login session.
    *       
    *  @param int $user_id     The user's ID.
    *  @access private
    *  @return void
    */
    private function _create($user_id) {
        $this->User_id = $user_id;
        session_regenerate_id(true);
        $_SESSION = array();
        $_SESSION['uid'] = $user_id;
        $_SESSION['fingerprint'] = $this->_get_fingerprint($user_id); 
        $_SESSION['last_active'] = $this->last_active;
        setcookie(self::COOKIE_ID_NAME, String::encrypt_cookie($user_id), time() + self::LOGIN_COOKIE_EXPIRE);
    }

   /**
    *  Executes when a session is destroyed.
    * 
    *  @param string $session_id        The session id.
    *  @access public
    *  @return bool
    */
    public function destroy($session_id) {
        $this->delete();
        return true;
    } 

   /**
    *  Executes when the garbage collector is executed.
    * 
    *  @param int $max_lifetime        The max session lifetime.
    *  @access public
    *  @return bool
    */
    public function gc($max_lifetime) {
        $expired = strtotime($this->last_active) - $max_lifetime;
        $where = '`last_active`<' . $expired;
        return $this->delete($where);
    }

   /**
    *  Returns the user's session fingerprint.
    *       
    *  @param int $user_id     The user's ID.     
    *  @access private
    *  @return string
    */
    private function _get_fingerprint($user_id) {
        return sha1(self::SESSION_SALT . $user_id . $_SERVER['HTTP_USER_AGENT']);
    }

    public function get_user_id() {
        return $this->User_id;
    }

    public function is_logged_in() {
        if ( (!isset($_SESSION['uid'])) || (!isset($_SESSION['fingerprint'])) || (!isset($_SESSION['last_active'])) ) {
            $this->User_id = 0;
            $is_logged_in = false;
        } elseif ( (!isset($_COOKIE[self::COOKIE_ID_NAME])) || ($_SESSION['uid'] !== String::decrypt_cookie($_COOKIE[self::COOKIE_ID_NAME])) || 
                 ($_SESSION['fingerprint'] !== $this->_get_fingerprint($_SESSION['uid'])) ) {
            $this->User_id = 0;
            $is_logged_in = false;
            $this->kill();
        } elseif ( (strtotime($_SESSION['last_active']) + self::SESSION_LIFETIME < time()) ) {
            $this->User_id = 0;
            $is_logged_in = false;
            $this->reset();
        } else {
            $this->User_id = $_SESSION['uid'];
            $is_logged_in = true;
            $_SESSION['last_active'] = $this->last_active;
        }
        return $is_logged_in;
    }

   /**
    *  Ends the current session and deletes the login cookie.
    *       
    *  @access public
    *  @return void
    */
    public function kill() {
        $_SESSION = array();
        setcookie(self::COOKIE_ID_NAME, '', time() - 3600);
        session_destroy();
    }
   
   /**
    *  Logs a user in.
    *
    *  @param obj $user               The user object to check against.
    *  @param obj $hashed_password    The encrypted password to check against.
    *  @param string $ip              The user's IP address.
    *  @access public
    *  @return bool
    */
    public function login($user, $hashed_password, $ip) {
        if ( !$user ) {
            return false; 
        }

        // Check that password matches
        if ( $user->password !== $hashed_password ) {
            return false;
        }

        // Format data
        $user->ip = sprintf("%u", ip2long($ip));
        $user->last_login = date('Y-m-d H:i:s');
        $user->store();
       
        $id = PRIMARY_KEY;
        $this->_create($user->$id); 

        return true;
    }

   /**
    *  Logs a user out.
    *
    *  @access public
    *  @return void
    */ 
    public function logout() {
        $this->kill();
    }

   /**
    *  Executes when the session is being opened.
    * 
    *  @access public
    *  @return bool
    */
    public function open() {
        return true;
    }

   /**
    *  Reads the session data.  MUST return a string for save handler
    *  to work as expected.
    * 
    *  @param string $session_id      The session id.
    *  @access public
    *  @return string
    */
    public function read($session_id) {
        $where = array(PRIMARY_KEY => $session_id);
        $this->_db->find('`data`', $this, $where);

        $data = $this->_db->fetch_column();
        return $data;
    }

   /**
    *  Ends the current session and starts a new one.
    *       
    *  @access public
    *  @return void
    */
    public function reset() {
        session_destroy();
        session_start();
        session_regenerate_id();
        $_SESSION = array();
    }

   /**
    *  Saves the session data.
    * 
    *  @param string $session_id        The session id.
    *  @param string $data              The session data.
    *  @access public
    *  @return int                      The lastInsertID.
    */
    public function write($session_id, $data) {
        $this->id = $session_id;
        $this->data = $data;

        return $this->store();       
    }
}

?>
