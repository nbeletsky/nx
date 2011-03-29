<?php
namespace lib;

class Form
{

    public function checkbox($object, $name, $value, $attributes=array())
    {
        $html = "<input type='checkbox' name='" . $this->_format_name($object, $name) . "' value='" . $value . "' ";
        $html .= $this->_parse_attributes($attributes);

        if ( $object->$name == $value )
        {
            $html .= "checked='checked' ";
        }
        $html .= "/>";
        
        return $html;
    }

    public function end()
    {
        return '</form>';
    }

    private function _format_name($object, $name)
    {
        $meta = new lib\Meta();
        $classname = $meta->classname_only($object);
        $id = PRIMARY_KEY;
        return 'data[' . $classname . '][' . $object->$id . '][' . $name . ']';
    }

    public function hidden($object, $name, $attributes=array())
    {
        $html = "<input type='hidden' name='" . $this->_format_name($object, $name) . "' value='" . $object->$name . "' ";
        $html .= $this->_parse_attributes($attributes);
        $html .= "/>";
    }

    private function _parse_attributes($attributes)
    {
        $html = '';
        foreach ( $attributes as $name=>$value )
        {
            $html .= $name . "='" . $value . "' "; 
        }
        return $html;
    }

    public function radios($object, $name, $values, $attributes=array())
    {
        $html = '';
        foreach ( $values as $value=>$display )
        {
            $html .= "<input type='radio' name='" . $this->_format_name($object, $name) . "' ";
            $html .= $this->_parse_attributes($attributes);
            if ( $object->$name == $value )
            {
                $html .= "checked='checked' ";
            }
            $html.= "/>";
        }

        return $html;
    }

    public function select($object, $name, $options, $attributes=array())
    {
        $html = "<select name='" . $this->_format_name($object, $name) . "' ";
        $html .= $this->_parse_attributes($attributes);
        $html .= ">";
        foreach( $options as $value=>$display )
        {
            $html.= "<option value='" . $value . "' ";
                
            if ( $object->$name == $value )
            {
                $html.= "selected='selected' ";
            }
            $html.= ">" . $display . "</option>";
        }
        $html.= "</select>";

        return $html;
    }

    public function start($action, $attributes=array())
    {
        $html = "<form method='post' action='" . $action . "'";
        $html .= $this->_parse_attributes($attributes);
        $html .= ">";
        
        return $html;
    }

    public function text($object, $name, $attributes=array())
    {
        $html = "<input type='text' name='" . $this->_format_name($object, $name) . " value='" . htmlentities($object->$name, ENT_QUOTES). "' "; 
        $html .= $this->_parse_attributes($attributes);
        $html .= "/>";
        
        return $html; 
    }

    public function textarea($object, $name, $attributes=array())
    {
        $html = "<textarea name='" . $this->_format_name($object, $name) . "' ";
        $html .= $this->_parse_attributes($attributes);
        $html .= '>'. htmlentities($object->$name) . "</textarea>"; 
        return $html;
    }

}

?>
