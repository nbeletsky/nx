<?php

/**
 * NX
 *
 * @author    Nick Sinopoli <NSinopoli@gmail.com>
 * @copyright Copyright (c) 2011, Nick Sinopoli
 * @license   http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace nx\lib;

/*
 *  The `Auth` class is used to create and validate
 *  form tokens.  These tokens help to ensure that
 *  any remote data sent to the server comes from the
 *  actual sender, and not via a CSRF.
 *
 *  @package lib
 */
class Auth {

   /**
    *  Creates a unique token for a given session.
    *
    *  @param string $salt           The token salt.
    *  @access public
    *  @return string
    */
    public static function create_token($salt = 'JU7]h{I^Wic)') {
        $token = sha1(microtime() . $salt);
        $_SESSION['token'] = $token;

        return $token;
    }

   /**
    *  Checks that the supplied token is valid for a given request.
    *
    *  @param array $request         The data provided with the http request.
    *  @access public
    *  @return bool
    */
    public static function is_token_valid($request) {
        return ( $request['token'] === $_SESSION['token'] );
    }

}

?>
