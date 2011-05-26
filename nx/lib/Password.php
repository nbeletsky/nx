<?php

namespace nx\lib;

class Password {

    public static function check($password, $stored_hash) {
        $hash = crypt($password, $stored_hash);
        return ( $hash == $stored_hash );
    }

    protected static function _gensalt_blowfish($input) {
        $itoa64 = './ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $iteration_count_log2 = 8;

        $output = '$2a$';
        $output .= chr(ord('0') + $iteration_count_log2 / 10);
        $output .= chr(ord('0') + $iteration_count_log2 % 10);
        $output .= '$';

        $i = 0;
        do {
            $c1 = ord($input[$i++]);
            $output .= $itoa64[$c1 >> 2];
            $c1 = ($c1 & 0x03) << 4;
            if ( $i >= 16 ) {
                $output .= $itoa64[$c1];
                break;
            }

            $c2 = ord($input[$i++]);
            $c1 |= $c2 >> 4;
            $output .= $itoa64[$c1];
            $c1 = ($c2 & 0x0f) << 2;

            $c2 = ord($input[$i++]);
            $c1 |= $c2 >> 6;
            $output .= $itoa64[$c1];
            $output .= $itoa64[$c2 & 0x3f];
        } while ( true );

        return $output;
    }

    public static function get_hash($password) {
        $random = self::_get_random_bytes(16);
        $hash = crypt($password, self::_gensalt_blowfish($random));
        if ( strlen($hash) == 60 ) {
            return $hash;
        }

        return false;
    }

    protected static function _get_random_bytes($count) {
        $output = '';
        $random_state = microtime() . getmypid();
        if ( is_readable('/dev/urandom') && ($fh = @fopen('/dev/urandom', 'rb')) ) {
            $output = fread($fh, $count);
            fclose($fh);
        }

        if ( strlen($output) < $count ) {
            $output = '';
            for ( $i = 0; $i < $count; $i += 16 ) {
                $random_state = hash('sha256', microtime() . $random_state);
                $output .= pack('H*', hash('sha256', $random_state));
            }
            $output = substr($output, 0, $count);
        }

        return $output;
    }

}

?>
