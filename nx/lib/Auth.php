<?php

namespace nx\lib;

class Auth {

   /**
    *  Creates a unique token for a given controller.
    *
    *  @param string $controller     The controller name. 
    *  @param string $salt           The token salt. 
    *  @access public
    *  @return string
    */
    public static function create_token($controller, $salt = 'JU7]h{I^Wic)') {
        $token = sha1(microtime() . $salt);
        $_SESSION[$controller . '_token'] = $token;

        return $token;
    }

   /**
    *  Checks that a controller's token is valid for a given request.
    *
    *  @param array $request         The data provided with the http request.
    *  @param string $controller     The controller name. 
    *  @access public
    *  @return bool
    */
    public static function is_token_valid($request, $controller) {
        return ( $request['token'] === $_SESSION[$controller . '_token'] );
    }

}

?>
