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
 *  The `Form` class is used to generate common HTML elements.
 *  All helper creation methods accept an optional `$binding` parameter,
 *  which can be used to autopopulate an instance of that object
 *  with the element's values upon form submission.
 *
 *  @see /nx/lib/Request::extract_post()
 *  @package lib
 */
class Form {

   /**
    *  Maintains the array index of bindings.
    *  (This is used when a Form method [e.g., Form->text()] is called
    *  multiple times with the same parameters.  An index is needed
    *  to ensure that both inputs are passed to the server
    *  under unique names.)
    *
    *  @var array
    *  @access protected
    */
    protected $_binding_counter = array();

   /**
    * Creates a checkbox.
    *
    * @param array $attributes          The HTML attributes.
    * @param obj $binding               The object to which the value of
    *                                   the checkbox should be mapped.
    * @access public
    * @return string
    */
    public function checkbox($attributes, $binding = null) {
        $html = "<input type='checkbox' ";
        $html .= $this->_parse_attributes($attributes, $binding);

        if (
            !is_null($binding)
            && isset($binding->$attributes['name'])
            && $binding->$attributes['name'] == $attributes['value']
        ) {
            $html .= "checked='checked' ";
        }
        $html .= "/>";

        return $html;
    }

   /**
    * Creates an email textbox.
    *
    * @param array $attributes          The HTML attributes.
    * @param obj $binding               The object to which the value of the
    *                                   textbox should be mapped.
    * @access public
    * @return string
    */
    public function email($attributes, $binding = null) {
        return $this->_input(array('type' => __FUNCTION__) + $attributes, $binding);
    }

   /**
    * Escapes a value for output in an HTML context.
    *
    * @param mixed $value
    * @access public
    * @return mixed
    */
    public function escape($value) {
        if ( is_array($value) ) {
            return array_map(array($this, __FUNCTION__), $value);
        }
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

   /**
    * Creates a hidden input.
    *
    * @param array $attributes          The HTML attributes.
    * @param obj $binding               The object to which the value of the
    *                                   hidden input should be mapped.
    * @access public
    * @return string
    */
    public function hidden($attributes, $binding = null) {
        return $this->_input(array('type' => __FUNCTION__) + $attributes, $binding);
    }

   /**
    * Creates an input field.
    *
    * @param array $attributes          The HTML attributes.
    * @param obj $binding               The object to which the value of the
    *                                   input should be mapped.
    * @access protected
    * @return string
    */
    protected function _input($attributes, $binding = null) {
        $html = "<input ";
        $html .= $this->_parse_attributes($attributes, $binding);
        $html .= "/>";

        return $html;
    }

   /**
    * Creates a number textbox.
    *
    * @param array $attributes          The HTML attributes.
    * @param obj $binding               The object to which the value of the textbox
    *                                   should be mapped.
    * @access public
    * @return string
    */
    public function number($attributes, $binding = null) {
        return $this->_input(array('type' => __FUNCTION__) + $attributes, $binding);
    }

   /**
    * Parses HTML attributes and binds an object's value to an element.
    *
    * @param array $attributes          The HTML attributes.
    * @param obj $binding               The object to which the value of the
    *                                   hidden input should be mapped.
    * @access protected
    * @return string
    */
    protected function _parse_attributes($attributes, $binding) {
        $html = '';
        $value_present = false;
        foreach ( $attributes as $key => $setting ) {
            // An attribute passed alone without a key (e.g., array('autofocus'))
            // will be assigned a numeric key by PHP
            if ( is_numeric($key) ) {
                $html .= $setting . " ";
                continue;
            }

            switch ( $key ) {
                case 'name':
                    if ( is_null($binding) ) {
                        break;
                    }

                    $b_class = $binding->classname();

                    if ( !is_null($binding->get_pk()) ) {
                        $setting = $b_class
                            . '|' . $binding->get_pk()
                            . '[' . $setting . "]";
                        break;
                    }

                    if ( !array_key_exists($b_class, $this->_binding_counter) ) {
                        $this->_binding_counter[$b_class] = array($setting);
                    } else {
                        $this->_binding_counter[$b_class][] = $setting;
                    }
                    $count = array_count_values($this->_binding_counter[$b_class]);
                    $index = $count[$setting] - 1;
                    $setting = $b_class . '[' . $index . '][' . $setting . "]";
                    break;
                case 'value':
                    $value_present = true;
                    break;
            }
            $html .= $key . "='" . $this->escape($setting) . "' ";
        }

        if (
            !$value_present
            && !is_null($binding)
            && !is_null($binding->$attributes['name'])
        ) {
            $html .= "value='" . $this->escape($binding->$attributes['name']) . "'";
        }

        return $html;
    }

   /**
    * Creates a set of radio buttons.
    *
    * @param array $attributes          The HTML attributes.
    * @param array $options             The values to use for the radio buttons.
    * @param obj $binding               The object to which the value of the
    *                                   radio buttons should be mapped.
    * @access public
    * @return string
    */
    public function radios($attributes, $values = array(), $binding = null) {
        $html = '';
        foreach ( $values as $value => $display ) {
            $html .= "<input type='radio' ";
            $html .= $this->_parse_attributes($attributes, $binding);
            if (
                !is_null($binding)
                && isset($binding->$attributes['name'])
                && $binding->$attributes['name'] == $value
            ) {
                $html .= "checked='checked' ";
            }
            $html.= "/>";
        }

        return $html;
    }

   /**
    * Creates a search textbox.
    *
    * @param array $attributes          The HTML attributes.
    * @param obj $binding               The object to which the value of the
    *                                   textbox should be mapped.
    * @access public
    * @return string
    */
    public function search($attributes, $binding = null) {
        return $this->_input(array('type' => __FUNCTION__) + $attributes, $binding);
    }

   /**
    * Creates a dropdown list.
    *
    * @param array $attributes          The HTML attributes.
    * @param array $options             The options with which to populate the dropdown list.
    * @param obj $binding               The object to which the value of the
    *                                   dropdown list should be mapped.
    * @access public
    * @return string
    */
    public function select($attributes, $options = array(), $binding = null) {
        $html = "<select ";
        $html .= $this->_parse_attributes($attributes, $binding);
        $html .= ">";
        foreach( $options as $value => $display ) {
            $html.= "<option value='" . $this->escape($value) . "' ";

            if (
                !is_null($binding)
                && isset($binding->$attributes['name'])
                && $binding->$attributes['name'] == $value
            ) {
                $html.= "selected='selected' ";
            }
            $html.= ">" . $this->escape($display) . "</option>";
        }
        $html.= "</select>";

        return $html;
    }

   /**
    * Creates a textbox.
    *
    * @param array $attributes          The HTML attributes.
    * @param obj $binding               The object to which the value of the
    *                                   textbox should be mapped.
    * @access public
    * @return string
    */
    public function text($attributes, $binding = null) {
        return $this->_input(array('type' => __FUNCTION__) + $attributes, $binding);
    }

   /**
    * Creates a textarea.
    *
    * @param array $attributes          The HTML attributes.
    * @param obj $binding               The object to which the value of the
    *                                   textarea should be mapped.
    * @access public
    * @return string
    */
    public function textarea($attributes, $binding = null) {
        $html = "<textarea ";
        $html .= $this->_parse_attributes($attributes, $binding);
        $html .= '>';
        if ( isset($attributes['value']) ) {
            $html .= $this->escape($attributes['value']);
        } elseif ( !is_null($binding) && isset($binding->$attributes['name']) ) {
            $html .= $this->escape($binding->$attributes['name']);
        }
        $html .= "</textarea>";

        return $html;
    }

}

?>
