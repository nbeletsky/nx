<?php

namespace lib;

use lib\Meta;

class Form {
    public function checkbox($object, $name, $type, $value, $attributes=array()) {
        $html = "<input type='checkbox' name='" . $this->_format_name($object, $name, $type) . "' value='" . $value . "' ";
        $html .= $this->_parse_attributes($attributes);

        if ( $object->$name == $value ) {
            $html .= "checked='checked' ";
        }
        $html .= "/>";
        
        return $html;
    }

    public function end() {
        return '</form>';
    }

    protected function _format_name($object, $name, $type) {
        $meta = new Meta();
        $classname = $meta->classname_only($object);
        $id = PRIMARY_KEY;
        // TODO: Ensure that $type is 's', 'f', or 'i'
        return '[' . $classname . '][' . $object->$id . '][' . $name . '|' . $type ']';
    }

    public function hidden($object, $name, $type, $attributes=array()) {
        $html = "<input type='hidden' name='" . $this->_format_name($object, $name, $type) . "' value='" . $object->$name . "' ";
        $html .= $this->_parse_attributes($attributes);
        $html .= "/>";
    }

    protected function _parse_attributes($attributes) {
        $html = '';
        foreach ( $attributes as $name=>$value ) {
            if ( !is_numeric($name) ) {
                $html .= $name . "='" . $value . "' "; 
            } else {
                $html .= $value . " "; 
            }
        }
        return $html;
    }

    public function radios($object, $name, $type, $values, $attributes=array()) {
        $html = '';
        foreach ( $values as $value=>$display ) {
            $html .= "<input type='radio' name='" . $this->_format_name($object, $name, $type) . "' ";
            $html .= $this->_parse_attributes($attributes);
            if ( $object->$name == $value ) {
                $html .= "checked='checked' ";
            }
            $html.= "/>";
        }

        return $html;
    }

    public function select($object, $name, $type, $options, $attributes=array()) {
        $html = "<select name='" . $this->_format_name($object, $name, $type) . "' ";
        $html .= $this->_parse_attributes($attributes);
        $html .= ">";
        foreach( $options as $value=>$display ) {
            $html.= "<option value='" . $value . "' ";
                
            if ( $object->$name == $value ) {
                $html.= "selected='selected' ";
            }
            $html.= ">" . $display . "</option>";
        }
        $html.= "</select>";

        return $html;
    }

    public function start($action, $attributes=array()) {
        $html = "<form method='post' action='" . $action . "'";
        $html .= $this->_parse_attributes($attributes);
        $html .= ">";
        
        return $html;
    }

    public function text($object, $name, $type, $attributes=array()) {
        $html = "<input type='text' name='" . $this->_format_name($object, $name, $type) . "' value='" . htmlentities($object->$name, ENT_QUOTES) . "' "; 
        $html .= $this->_parse_attributes($attributes);
        $html .= "/>";
        
        return $html; 
    }

    public function textarea($object, $name, $type, $attributes=array()) {
        $html = "<textarea name='" . $this->_format_name($object, $name, $type) . "' ";
        $html .= $this->_parse_attributes($attributes);
        $html .= '>'. htmlentities($object->$name) . "</textarea>"; 
        return $html;
    }

}

?>
