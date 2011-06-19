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
 *  The `String` class contains methods that help with
 *  string manipulation.
 *
 *  @package lib
 */
class String {

   /**
    *  Returns the string between two delimiters in a body of text.
    *
    *  @param string $start         The beginning delimiter.
    *  @param string $end           The ending delimiter.
    *  @param string $body          The text containing the delimiters.
    *  @access public
    *  @return string
    */
    public static function between($start, $end, $body) {
        $start_pos = strpos($body, $start) + strlen($start);
        $end_pos = strpos($body, $end);
        $between = substr($body, $start_pos, $end_pos - $start_pos);
        return ( $between ) ? trim($between) : '';
    }

   /**
    *  Decrypts cookie user ID.
    *
    *  @param string $hex_hash      The hash to be decrypted.
    *  @access public
    *  @return int
    */
    public static function decrypt_cookie($hex_hash) {
        $hex_hash = filter_var($hex_hash, FILTER_SANITIZE_STRING);
        if ( strlen($hex_hash) !== 40 ) {
            return false;
        }
        // Extrapolate hex from hash
        $cur_pos = 0;
        $hex_id = '';
        for ( $i = 0; $i <= 7; $i++ ) {
            $cur_pos += $i + 1;
            $hex_id .= substr($hex_hash, $cur_pos, 1);
        }
        // Convert hex to user id
        return hexdec($hex_id);
    }

   /**
    *  Encrypts user ID for cookie use.
    *
    *  @param int $user_id      The user's ID.
    *  @access public
    *  @return string
    */
    public static function encrypt_cookie($user_id) {
        // Create the hash
        $hex_salt = 'R1c?+r.VEfIN';
        $hex_hash = sha1($hex_salt . $user_id);
        // Convert user id to 8-digit hex
        $user_hex = str_pad(dechex($user_id), 8, '0', STR_PAD_LEFT);
        // Interpolate hex into hash
        $cur_pos = 0;
        for ( $i = 0; $i <= 7; $i++ ) {
            $cur_pos += $i + 1;
            $hex_hash = substr_replace(
                $hex_hash,
                substr($user_hex, $i, 1),
                $cur_pos,
                1
            );
        }
        return $hex_hash;
    }

}

?>
