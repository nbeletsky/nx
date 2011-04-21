<?php

namespace nx\lib;

use nx\lib\Meta;

class Form {

    public static function checkbox($attributes, $binding = null) {
        $html = "<input type='checkbox' ";
        $html .= self::_parse_attributes($attributes, $binding);

        if ( !is_null($binding) && isset($binding->$attributes['name']) && isset($binding->$attributes['name']) && $binding->$attributes['name'] == $attributes['value'] ) {
            $html .= "checked='checked' ";
        }
        $html .= "/>";
        
        return $html;
    }

    public static function end() {
        return '</form>';
    }

    public static function hidden($attributes, $binding = null) {
        $html = "<input type='hidden' ";
        $html .= self::_parse_attributes($attributes, $binding);
        $html .= "/>";
    }

    protected function _parse_attributes($attributes, $binding) {
        $html = '';
        $value_present = false;
        foreach ( $attributes as $key => $setting ) {
            // An attribute passed alone without a key (e.g., array('autofocus'))
            // will be assigned a numeric key by PHP
            if ( is_numeric($key) ) {
                $html .= $setting . " "; 
            } else {
                switch ( $key ) {
                    case 'name':
                        if ( !is_null($binding) ) {
                            $id = PRIMARY_KEY;
                            if ( isset($binding->$id) ) {
                                $setting = '[' . $binding->classname() . '|' . $binding->$id . '][' . $setting . ']';
                            } else {
                                $setting = '[' . $binding->classname() . '][][' . $setting . ']';
                            }
                        }
                        break;
                    case 'value':
                        $value_present = true;
                        break;
                }
                $html .= $key . "='" . $setting . "' "; 
            } 
        }

        if ( !$value_present && !is_null($binding) && isset($binding->$attributes['name']) ) {
            $html .= "value='" . htmlentities($binding->$attributes['name'], ENT_QUOTES) . "'";
        } 
        return $html;
    }

    public static function radios($attributes, $values = array(), $binding = null) {
        $html = '';
        foreach ( $values as $value => $display ) {
            $html .= "<input type='radio' ";
            $html .= self::_parse_attributes($attributes, $binding);
            if ( !is_null($binding) && isset($binding->$attributes['name']) && $binding->$attributes['name'] == $value ) {
                $html .= "checked='checked' ";
            }
            $html.= "/>";
        }

        return $html;
    }

    public static function select($attributes, $options = array(), $binding = null) {
        $html = "<select ";
        $html .= self::_parse_attributes($attributes, $binding);
        $html .= ">";
        foreach( $options as $value => $display ) {
            $html.= "<option value='" . $value . "' ";
                
            if ( $object->$name == $value ) {
                $html.= "selected='selected' ";
            }
            $html.= ">" . $display . "</option>";
        }
        $html.= "</select>";

        return $html;
    }

    public static function start($attributes) {
        $html = "<form method='post' ";
        $html .= self::_parse_attributes($attributes, $binding);
        $html .= ">";
        
        return $html;
    }

    public static function text($attributes, $binding = null) {
        $html = "<input type='text' "; 
        $html .= self::_parse_attributes($attributes, $binding);
        $html .= "/>";
        
        return $html; 
    }

    public static function textarea($attributes, $binding = null) {
        $html = "<textarea ";
        $html .= self::_parse_attributes($attributes, $binding);
        $html .= '>';
        if ( isset($attributes['value']) ) {
            $html .= htmlentities($attributes['value'], ENT_QUOTES); 
        } elseif ( !is_null($binding) && isset($binding->$attributes['name']) ) {
            $html .= htmlentities($binding->$attributes['name'], ENT_QUOTES); 
        }
        $html .= "</textarea>"; 
        return $html;
    }

}

?>
