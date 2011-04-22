<?php

namespace nx\lib;

class Validator {

    public static function alphanumeric($value) {
        return preg_match('/^[\p{Ll}\p{Lm}\p{Lo}\p{Lt}\p{Lu}\p{Nd}]+$/mu', $value);
    }

    public static function decimal($value, $options = array()) {
        if ( isset($options['precision']) ) {
            $precision = strlen($value) - strrpos($value, '.') - 1;

            if ( $precision !== (int) $options['precision'] ) {
                return false;
            }
        }
        return ( filter_var($value, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE) !== null );
    }

    public static function ip($value, $options = array()) {
        $options += array('flags' => array());
        return (boolean) filter_var($value, FILTER_VALIDATE_IP, $options);
    }

    public static function length_between($value, $options = array()) {
        $options += array('min' => 1, 'max' => 255);
        $length = strlen($value);
        return ( $length >= $options['min'] && $length <= $options['max'] );

    }

    public static function not_empty($value) {
        return preg_match('/[^\s]+/m', $value);
    }

    public static function numeric($value) {
        return is_numeric($value);
    }

    public static function within_bounds($value, $options = array()) {
        if (!is_numeric($value)) {
            return false;
        }

        $options += array('upper' => null, 'lower' => null);

        if ( !is_null($options['upper']) && !is_null($options['lower']) ) {
            return ( $value > $options['lower'] && $value < $options['upper'] );
        } elseif ( !is_null($options['upper']) ) {
            return ( $value < $options['upper'] );
        } elseif ( !is_null($options['lower']) ) {
            return ( $value > $options['lower'] );
        }

        return is_finite($value);
    }

}

?>
