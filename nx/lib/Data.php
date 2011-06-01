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
    *  Extracts $_POST data and returns it as a collection of objects (if an
    *  object was bound to it via the form) and `key` => `value` pairs (if no 
    *  object is bound).
    * 
    *  @param array $data      The $_POST data.
    *  @access public
    *  @return mixed
    */
    public static function extract_post($data) {
        $collection = array();
        foreach ( $data as $child_key => $child ) {
            if ( !is_array($child) ) { // name = 'username'
                $collection[$child_key] = $child; 
            } else {
                $loc = strrpos($child_key, '|');
                if ( $loc !== false ) { // name = 'User|id[username]'
                    $id = substr($child_key, $loc + 1);
                    $class = substr($child_key, 0, $loc);
                    $obj = new $class(array('id' => $id));
                    foreach ( $child as $key => $value )
                    {
                        $obj->$key = $value;
                    }
                    $collection[$class][] = $obj; 
                } else { // name = 'User[][username]'
                    foreach ( $child as $grandchild_array ) {
                        $obj_name = 'app\model\\' . $child_key;
                        $obj = new $obj_name();
                        foreach ( $grandchild_array as $key => $value ) {
                            $obj->$key = $value;
                        }
                    }
                    $collection[$child_key][] = $obj; 
                }
            }
        }

        return $collection; 
    }

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
                $data = floatval(filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
                break;
            case 'i':
                $data = intval(filter_var($data, FILTER_SANITIZE_NUMBER_INT));
                break;
            case 's':
                $data = trim(strval(filter_var($data, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_ENCODE_HIGH | FILTER_FLAG_ENCODE_AMP)));
                break;
        }
        return $data;
    }

}

?>
