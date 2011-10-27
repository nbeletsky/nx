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
 *  The `Validator` class contains methods that validate
 *  data.
 *
 *  @package lib
 */
class Validator {

   /**
    *  Checks a string to see if it consists of nothing but alphanumeric characters.
    *
    *  @param string $value         The string to be checked.
    *  @access public
    *  @return bool
    */
    public static function alphanumeric($value) {
        $pattern = '/^[\p{Ll}\p{Lm}\p{Lo}\p{Lt}\p{Lu}\p{Nd}]+$/mu';
        return (boolean) preg_match($pattern, $value);
    }

   /**
    *  Checks a date to see if it is in the specified format.
    *
    *  @param string $value         The string to be checked.
    *  @param array $options        Accepts `format` as a key.
    *                               Possible formats are as follows:
    *                               'dmy' - 20-10-2011 or 20-10-11
    *                               (separator: space, period, dash,
    *                               or forward slash)
    *
    *                               'mdy' - 10-20-2011 or 10-20-11
    *                               (separator: space, period, dash,
    *                               or forward slash)
    *
    *                               'ymd' - 2011-10-20 or 11-10-20
    *                               (separator: space, period, dash,
    *                               or forward slash)
    *
    *                               'dMy' - 20 October 2011 or 20 Oct 2011
    *
    *                               'Mdy' - October 20, 2011 or Oct 20, 2011
    *                               (comma is optional)
    *
    *                               'My'  - October 2011 or Oct 2011
    *
    *                               'my'  - 10/2011
    *                               (separator: space, period, dash,
    *                               or forward slash)
    *
    *                               'dmy hms' - 20/10-2011 17:21:12
    *                               (date separator: period, dash,
    *                               or forward slash)
    *  @access public
    *  @return bool
    */
    public static function date($value, $options = array()) {
        $formats = array(
            'dmy'     => '%^(?:(?:31(\\/|-|\\.|\\x20)(?:0?[13578]|1[02]))\\1|(?:(?:29|30)' .
                         '(\\/|-|\\.|\\x20)(?:0?[1,3-9]|1[0-2])\\2))(?:(?:1[6-9]|[2-9]\\d)?' .
                         '\\d{2})$|^(?:29(\\/|-|\\.|\\x20)0?2\\3(?:(?:(?:1[6-9]|[2-9]\\d)?' .
                         '(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])' .
                         '00))))$|^(?:0?[1-9]|1\\d|2[0-8])(\\/|-|\\.|\\x20)(?:(?:0?[1-9])|' .
                         '(?:1[0-2]))\\4(?:(?:1[6-9]|[2-9]\\d)?\\d{2})$%',

            'mdy'     => '%^(?:(?:(?:0?[13578]|1[02])(\\/|-|\\.|\\x20)31)\\1|(?:(?:0?[13-9]|' .
                         '1[0-2])(\\/|-|\\.|\\x20)(?:29|30)\\2))(?:(?:1[6-9]|[2-9]\\d)?\\d' .
                         '{2})$|^(?:0?2(\\/|-|\\.|\\x20)29\\3(?:(?:(?:1[6-9]|[2-9]\\d)?' .
                         '(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])' .
                         '00))))$|^(?:(?:0?[1-9])|(?:1[0-2]))(\\/|-|\\.|\\x20)(?:0?[1-9]|1' .
                         '\\d|2[0-8])\\4(?:(?:1[6-9]|[2-9]\\d)?\\d{2})$%',

            'ymd'     => '%^(?:(?:(?:(?:(?:1[6-9]|[2-9]\\d)?(?:0[48]|[2468][048]|[13579]' .
                         '[26])|(?:(?:16|[2468][048]|[3579][26])00)))(\\/|-|\\.|\\x20)' .
                         '(?:0?2\\1(?:29)))|(?:(?:(?:1[6-9]|[2-9]\\d)?\\d{2})(\\/|-|\\.|' .
                         '\\x20)(?:(?:(?:0?[13578]|1[02])\\2(?:31))|(?:(?:0?[1,3-9]|1[0-2])' .
                         '\\2(29|30))|(?:(?:0?[1-9])|(?:1[0-2]))\\2(?:0?[1-9]|1\\d|2[0-8]' .
                         '))))$%',

            'dMy'     => '/^((31(?!\\ (Feb(ruary)?|Apr(il)?|June?|(Sep(?=\\b|t)t?|Nov)' .
                         '(ember)?)))|((30|29)(?!\\ Feb(ruary)?))|(29(?=\\ Feb(ruary)?\\ ' .
                         '(((1[6-9]|[2-9]\\d)(0[48]|[2468][048]|[13579][26])|((16|[2468]' .
                         '[048]|[3579][26])00)))))|(0?[1-9])|1\\d|2[0-8])\\ (Jan(uary)?|' .
                         'Feb(ruary)?|Ma(r(ch)?|y)|Apr(il)?|Ju((ly?)|(ne?))|Aug(ust)?|' .
                         'Oct(ober)?|(Sep(?=\\b|t)t?|Nov|Dec)(ember)?)\\ ((1[6-9]|[2-9]' .
                         '\\d)\\d{2})$/',

            'Mdy'     => '/^(?:(((Jan(uary)?|Ma(r(ch)?|y)|Jul(y)?|Aug(ust)?|Oct(ober)?' .
                         '|Dec(ember)?)\\ 31)|((Jan(uary)?|Ma(r(ch)?|y)|Apr(il)?|Ju((ly?)' .
                         '|(ne?))|Aug(ust)?|Oct(ober)?|(Sept|Nov|Dec)(ember)?)\\ (0?[1-9]' .
                         '|([12]\\d)|30))|(Feb(ruary)?\\ (0?[1-9]|1\\d|2[0-8]|(29(?=,?\\ ' .
                         '((1[6-9]|[2-9]\\d)(0[48]|[2468][048]|[13579][26])|((16|[2468]' .
                         '[048]|[3579][26])00)))))))\\,?\\ ((1[6-9]|[2-9]\\d)\\d{2}))$/',

            'My'      => '%^(Jan(uary)?|Feb(ruary)?|Ma(r(ch)?|y)|Apr(il)?|Ju((ly?)|(ne?))|' .
                         'Aug(ust)?|Oct(ober)?|(Sep(?=\\b|t)t?|Nov|Dec)(ember)?)[ /]((1[6-9]' .
                         '|[2-9]\\d)\\d{2})$%',

            'my'      => '%^(((0[123456789]|10|11|12)([- /.])(([1][9][0-9][0-9])|([2][0-9]' .
                         '[0-9][0-9]))))$%',

            'dmy hms' => '%^(?=\d)(?:(?:31(?!.(?:0?[2469]|11))|(?:30|29)(?!.0?2)' .
                         '|29(?=.0?2.(?:(?:(?:1[6-9]|[2-9]\d)?(?:0[48]|[2468][048]' .
                         '|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00)))' .
                         '(?:\x20|$))|(?:2[0-8]|1\d|0?[1-9]))([-./])(?:1[012]|0?' .
                         '[1-9])\1(?:1[6-9]|[2-9]\d)?\d\d(?:(?=\x20\d)\x20|$))?' .
                         '(((0?[1-9]|1[012])(:[0-5]\d){0,2}(\x20[AP]M))|([01]\d|' .
                         '2[0-3])(:[0-5]\d){1,2})?$%'
        );

        if ( !isset($options['format']) || !isset($formats[$options['format']]) ) {
            return false;
        }

        return (boolean) preg_match($formats[$options['format']], $value);
    }

   /**
    *  Checks a decimal number.
    *
    *  @param mixed $value          The value to be checked.
    *  @param array $options        Accepts `precision` as a key.
    *                               If supplied, a check that the decimal has
    *                               the same level of precision will be made.
    *  @access public
    *  @return bool
    */
    public static function decimal($value, $options = array()) {
        if ( isset($options['precision']) ) {
            $decimal_position = strrpos($value, '.');
            if ( $decimal_position === false ) {
                return false;
            }

            $precision = strlen($value) - $decimal_position - 1;
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
        return (boolean) filter_var($value, FILTER_VALIDATE_EMAIL);
    }

   /**
    *  Checks an ip address.
    *
    *  @param string $value          The ip address to be checked.
    *  @param array $options         The options to be used by the filter. See
    *                                http://us3.php.net/manual/en/filter.filters.validate.php
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
    *  @param array $options         The options by which to constrain the check.
    *                                Takes `min` and/or `max` as keys.
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
        return (boolean) preg_match('/[^\s]+/m', $value);
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
    *  Checks that a number is within certain bounds.
    *
    *  @param mixed $value           The value to be checked.
    *  @param array $options         The options by which to constrain the check.
    *                                Takes `upper` and/or `lower` as keys.
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
    public static function zip_code($value) {
        return (boolean) preg_match('/^\d{5}([\-]\d{4})?$/', $value);
    }

}

?>
