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
 *  The `Data` class is a collection of data-oriented methods, which
 *  help sanitize and organize remote data.
 *
 *  @package lib
 */
class Data {

   /**
    *  Sanitizes input according to type.
    *
    *  @param mixed $data      The data to be sanitized.
    *  @param string $type     The type of validation.
    *  @access public
    *  @return mixed
    */
    public static function sanitize($data, $type) {
        switch ( $type ) {
            case 'b':
                $data = (boolean) filter_var($data, FILTER_SANITIZE_NUMBER_INT);
                break;
            case 'f':
                $data = floatval(filter_var(
                    $data,
                    FILTER_SANITIZE_NUMBER_FLOAT,
                    FILTER_FLAG_ALLOW_FRACTION
                ));
                break;
            case 'i':
                $data = intval(filter_var($data, FILTER_SANITIZE_NUMBER_INT));
                break;
            case 's':
                $data = trim(strval(filter_var(
                    $data,
                    FILTER_SANITIZE_SPECIAL_CHARS,
                    FILTER_FLAG_STRIP_LOW | FILTER_FLAG_ENCODE_HIGH | FILTER_FLAG_ENCODE_AMP
                )));
                break;
        }
        return $data;
    }

}

?>
