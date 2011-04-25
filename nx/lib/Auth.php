<?php

namespace nx\lib;

class Auth {

    public static function create_token($salt, $controller) {
        $token = sha1(microtime() . $salt);
        $_SESSION[$controller . '_token'] = $token;

        return $token;
    }

    public static function is_token_valid($request, $controller) {
        return ( $request['token'] === $_SESSION[$controller . '_token'] );
    }

}

?>
