<?php

namespace nx\lib;

class String {

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
            $hex_hash = substr_replace($hex_hash, substr($user_hex, $i, 1), $cur_pos, 1);
        }
        return $hex_hash;
    }

   /**
    *  Encrypts the password.  First, we create a salt based on the password itself.
    *  We then shorten this salt to be the same length as the original password.  We take
    *  this shortened salt, combine it with the password, and hash it.  We then shorten this
    *  new hashed password, and combine it with the shortened salt, giving us a hash length
    *  equivalent to the hash length determined by the encryption technique.
    *
    *  @param string $content           String to be encrypted.
    *  @param $enc_technique            The encryption technique to be used.
    *  @access public
    *  @return string
    */ 
    public static function encrypt_password($content, $enc_technique = 'sha256') {
        $password_length = strlen($content); 
        // TODO: Fix salt construction
        switch ( $password_length % 4 ) {
            case 0:
                $salt = substr($content, 0, 2) . substr($content, -2) . substr($content, 2, -2);
                break;
            case 1:
                $salt = substr($content, -2) . substr($content, 2, -2) . substr($content, 0, 2);
                break;
            case 2:
                $salt = substr($content, 3, -3) . substr($content, -3) . substr($content, 0, 3);
                break;
            case 3:
                $salt = substr($content, 0, 3) . substr($content, 3, -3) . substr($content, -3);
                break;
        }

        $salt = hash($enc_technique, $salt);
        $hash_length = strlen($salt);
        
        // Shorten the salt based on the length of the password
        $salt = substr($salt, 0, $password_length);
        
        // Get used chars (this will ensure that the salt + salted password length is the same length as a regular hash)
        $used_chars = ($hash_length - $password_length) * -1; 
        
        // Hash the salt and password, and then combine our original hashed salt with our new salted password
        switch ( $password_length % 2 ) {
            case 0: 
                $salted_password = hash($enc_technique, $salt . $content);
                $final_result = $salt . substr($salted_password, $used_chars);
                break;
            case 1:
                $salted_password = hash($enc_technique, $content . $salt);
                $final_result = substr($salted_password, $used_chars) . $salt;
                break;
        }
        
        return $final_result;
    }

}

?>
