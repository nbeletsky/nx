<?php

namespace app\model;

use nx\lib\Password;
use nx\lib\String;

class Session extends \nx\core\Model {

   /**
    *  The session id.
    *
    *  @var int $id
    *  @access protected
    */
    protected $id;

   /**
    *  The session data.
    *
    *  @var string $id
    *  @access protected
    */
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

   /**
    *  Loads the configuration settings for the session.
    *
    *  @param array $config         The configuration settings,
    *                               which can take five options:
    *                               `no_cache`            - Whether or not to
    *                                                       cache the session.
    *                               `session_lifetime`    - The length of a
    *                                                       session.
    *                               `login_cookie_expire` - The cookie expiration
    *                                                       date.
    *                               `session_salt`        - The salt for session
    *                                                       encryption.
    *                               `cookie_id_name`      - Name of the cookie for
    *                                                       the user.
    *  @access public
    *  @return void
    */
    public function __construct(array $config = array()) {
        $defaults = array(
            'no_cache'            => true,
            'session_lifetime'    => 3600,  // 60 minutes
            'login_cookie_expire' => 2592000,  // 30 days
            'session_salt'        => 'M^mc?(9%ZKx[',
            'cookie_id_name'      => 'nara_id'
        );
        parent::__construct($config + $defaults);
    }

   /**
    *  Initializes the session handler.
    *
    *  @access protected
    *  @return void
    */
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
    *  @param int $user_id          The user's ID.
    *  @access private
    *  @return bool
    */
    private function _create($user_id) {
        $this->User_id = $user_id;

        session_regenerate_id(true);
        $_SESSION = array();
        $_SESSION['user_id'] = $user_id;
        $_SESSION['fingerprint'] = $this->_get_fingerprint($user_id);
        $_SESSION['last_active'] = $this->last_active;
        setcookie(
            $this->_config['cookie_id_name'],
            String::encrypt_cookie($user_id),
            time() + $this->_config['login_cookie_expire']
        );

        return true;
    }

   /**
    *  Executes when a session is destroyed.
    *
    *  @param string $session_id    The session id.
    *  @access public
    *  @return bool
    */
    public function destroy($session_id) {
        $where = array($this->_meta['key'] => $session_id);
        $this->delete($where);
        return true;
    }

   /**
    *  Executes when the garbage collector is executed.
    *
    *  @param int $max_lifetime     The max session lifetime.
    *  @access public
    *  @return bool
    */
    public function gc($max_lifetime) {
        $expired = strtotime($this->last_active) - $max_lifetime;
        $where = array(
            'last_active' => array(
                'lt' => $expired
            )
        );
        return $this->delete($where);
    }

   /**
    *  Returns the user's session fingerprint.
    *
    *  @param int $user_id          The user's ID.
    *  @access private
    *  @return string
    */
    private function _get_fingerprint($user_id) {
        return sha1($this->_config['session_salt'] . $user_id . $_SERVER['HTTP_USER_AGENT']);
    }

   /**
    *  Returns the user's id.
    *
    *  @access public
    *  @return int
    */
    public function get_user_id() {
        return $this->User_id;
    }

   /**
    *  Checks the user's current session status.  If the session
    *  has expired or is invalid, the user will automatically be
    *  logged out.
    *
    *  @access public
    *  @return bool
    */
    public function is_logged_in() {
        if (
            !isset($_SESSION['user_id'])
            || !isset($_SESSION['fingerprint'])
            || !isset($_SESSION['last_active'])
        ) {
            $this->User_id = 0;
            return false;
        }

        $uid = String::decrypt_cookie($_COOKIE[$this->_config['cookie_id_name']]);
        $fingerprint = $this->_get_fingerprint($_SESSION['user_id']);
        if (
            !isset($_COOKIE[$this->_config['cookie_id_name']])
            || $_SESSION['user_id'] != $uid
            || $_SESSION['fingerprint'] != $fingerprint
        ) {
            $this->User_id = 0;
            $this->logout();
            return false;
        }

        if ( strtotime($_SESSION['last_active']) + $this->_config['session_lifetime'] < time() ) {
            $this->User_id = 0;
            $this->reset();
            return false;
        }

        $this->User_id = $_SESSION['user_id'];
        $_SESSION['last_active'] = $this->last_active;
        return true;
    }

   /**
    *  Logs a user in.
    *
    *  @param obj $user             The user object to check against.
    *  @param string $password      The password to check against.
    *  @param string $ip            The user's IP address.
    *  @access public
    *  @return bool
    */
    public function login($user, $password, $ip) {
        if ( !$user ) {
            return false;
        }

        if ( !Password::check($password, $user->password) ) {
            return false;
        }

        $user->ip = sprintf('%u', ip2long($ip));
        $user->last_login = date('Y-m-d H:i:s');
        $user->store();

        $this->_create($user->get_pk());

        return true;
    }

   /**
    *  Ends the current session and deletes the login cookie.
    *
    *  @access public
    *  @return void
    */
    public function logout() {
        $_SESSION = array();
        setcookie($this->_config['cookie_id_name'], '', time() - 3600);
        session_destroy();
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
    *  Reads the session data.  Note that this MUST return a string
    *  for save handler to work as expected.
    *
    *  @param string $session_id    The session id.
    *  @access public
    *  @return string
    */
    public function read($session_id) {
        $where = array($this->_meta['key'] => $session_id);
        $this->_db->find('`data`', $this->classname(), $where);

        return $this->_db->fetch_column();
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
    *  @param string $session_id    The session id.
    *  @param string $data          The session data.
    *  @access public
    *  @return bool
    */
    public function write($session_id, $data) {
        $id = $this->_meta['key'];
        $this->$id = $session_id;
        $this->data = $data;

        return $this->store();
    }
}

?>
