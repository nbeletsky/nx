<?php

namespace nx\lib;

class Validator {

   /**
    *  Checks a string to see if it consists of nothing but alphanumeric characters.
    *
    *  @param string $value          The string to be checked.
    *  @access public
    *  @return bool
    */
    public static function alphanumeric($value) {
        return preg_match('/^[\p{Ll}\p{Lm}\p{Lo}\p{Lt}\p{Lu}\p{Nd}]+$/mu', $value);
    }

   /**
    *  Checks a decimal number.
    *
    *  @param mixed $value          The value to be checked.
    *  @param array $options        Accepts `precision` as a key.  If supplied, a check that the decimal has the same level of precision will be made.
    *  @access public
    *  @return bool
    */
    public static function decimal($value, $options = array()) {
        if ( isset($options['precision']) ) {
            $precision = strlen($value) - strrpos($value, '.') - 1;

            if ( $precision !== (int) $options['precision'] ) {
                return false;
            }
        }
        return ( filter_var($value, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE) !== null );
    }

   /**
    *  Checks an email address.
    *
    *  @param mixed $value          The email address to be checked.
    *  @access public
    *  @return bool
    */
    public static function email($value) {
        return filter_var($value, FILTER_VALIDATE_EMAIL);
    }

   /**
    *  Checks an ip address.
    *
    *  @param string $value          The ip address to be checked.
    *  @param array $options         The options to be used by the filter. See http://us3.php.net/manual/en/filter.filters.validate.php
    *  @access public
    *  @return bool
    */
    public static function ip($value, $options = array()) {
        $options += array('flags' => array());
        return (boolean) filter_var($value, FILTER_VALIDATE_IP, $options);
    }

   /**
    *  Checks that a string is between a certain length.
    *
    *  @param string $value          The string to be checked.
    *  @param array $options         The options by which to constrain the check.  Takes `min` and/or `max` as keys.
    *  @access public
    *  @return bool
    */
    public static function length_between($value, $options = array()) {
        $options += array('min' => 1, 'max' => 255);
        $length = strlen($value);
        return ( $length >= $options['min'] && $length <= $options['max'] );

    }

   /**
    *  Checks that a string is not empty.
    *
    *  @param string $value          The string to be checked.
    *  @access public
    *  @return bool
    */
    public static function not_empty($value) {
        return preg_match('/[^\s]+/m', $value);
    }

   /**
    *  Checks that a value is numeric.
    *
    *  @param mixed $value          The value to be checked.
    *  @access public
    *  @return bool
    */
    public static function numeric($value) {
        return is_numeric($value);
    }

   /**
    *  Checks that a password is constructed properly
    *  and has an acceptable length.
    * 
    *  @param string $value          The password to be checked.
    *  @param array $options         The options by which to constrain the check.  Takes `special_chars`, `min_length`, 
    *                                and/or `max_length` as keys.
    *  @access public
    *  @return bool
    */
    public static function password($value, $options = array()) {
        $options += array('special_chars' => '#@!$%._', 'min_length' => 5, 'max_length' => 16);

        $preg_pass = '/^(((?=.*[A-Za-z])(?=.*[\d])(?!.*[^A-Za-z\d]))|' . 
                     '((?=.*[A-Za-z])(?=.*[' . $options['special_chars'] . '])(?!.*[^A-Za-z' . $options['special_chars'] . ']))|' .
                     '((?=.*[\d])(?=.*[' . $options['special_chars'] . '])(?!.*[^\d' . $options['special_chars'] . ']))|' .
                     '((?=.*[A-Za-z])(?=.*[\d])(?=.*[' . $options['special_chars'] . '])(?!.*[^A-Za-z\d' . 
                     $options['special_chars'] . ']))).{' . $options['min_length'] . ',' . $options['max_length'] . '}$/';
        return preg_match($preg_pass, $value);
    }

   /**
    *  Checks that a number is within certain bounds.
    *
    *  @param mixed $value           The value to be checked.
    *  @param array $options         The options by which to constrain the check.  Takes `upper` and/or `lower` as keys.
    *  @access public
    *  @return bool
    */
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

   /**
    *  Checks a zip code.
    * 
    *  @param string $value            The zip code to be checked.
    *  @access public
    *  @return bool
    */
    public static function zip($value) {
        return preg_match('/^\d{5}([\-]\d{4})?$/', $value); 
    }

}

?>
