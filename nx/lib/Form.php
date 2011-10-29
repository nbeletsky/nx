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
            && isset($attributes['name'])
            && isset($attributes['value'])
            && property_exists($binding, $attributes['name'])
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
    * @param array $options             The parsing options.  Takes the
    *                                   following keys:
    *                                   'is_radio' - whether or not
    *                                   the HTML element is a radio button
    *                                   'is_select' - whether or not
    *                                   the HTML element is a select list
    *                                   'is_textarea' - whether or not
    *                                   the HTML element is a textarea
    * @access protected
    * @return string
    */
    protected function _parse_attributes($attributes, $binding = null, $options = array()) {
        $options += array(
            'is_radio'    => false,
            'is_select'   => false,
            'is_textarea' => false
        );
        $html = '';
        $value_present = false;
        foreach ( $attributes as $key => $setting ) {
            // An attribute passed alone without a key (e.g., array('autofocus'))
            // will be assigned a numeric key by PHP
            if ( is_numeric($key) ) {
                $html .= $this->escape($setting) . " ";
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
                        $this->_binding_counter[$b_class] = array();
                    }

                    $object_id = spl_object_hash($binding);
                    if ( !in_array($object_id, $this->_binding_counter[$b_class]) ) {
                        $this->_binding_counter[$b_class][] = $object_id;
                    }
                    $index = array_search($object_id, $this->_binding_counter[$b_class]);
                    $setting = $b_class . '[' . $index . '][' . $setting . "]";
                    break;
                case 'value':
                    $value_present = true;
                    break;
            }
            $html .= $this->escape($key) . "='" . $this->escape($setting) . "' ";
        }

        if (
            !$value_present
            && !$options['is_radio']
            && !$options['is_select']
            && !$options['is_textarea']
            && !is_null($binding)
            && isset($attributes['name'])
            && property_exists($binding, $attributes['name'])
            && !is_null($binding->$attributes['name'])
        ) {
            $html .= "value='" . $this->escape($binding->$attributes['name']) . "' ";
        }

        return $html;
    }

   /**
    * Creates a set of radio buttons.
    *
    * @param array $attributes          The HTML attributes.
    *                                   Key 'id' takes an array of ids, and
    *                                   key 'value' can be in the format of:
    *                                   'value' => 'display'
    *                                   (radio value will be set to 'value',
    *                                   and the text to the right of the
    *                                   radio will be set to 'display')
    *                                   or as an array without keys
    *                                   (both radio value and the text to the
    *                                   right of the radio will be set to the
    *                                   passed value)
    * @param obj $binding               The object to which the value of the
    *                                   radio buttons should be mapped.
    * @access public
    * @return string
    */
    public function radios($attributes, $binding = null) {
        $options = array('is_radio' => true);
        $html = '';
        $values = $attributes['value'];
        unset($attributes['value']);
        // Array is not associative
        if ( $values === array_values($values) ) {
            $values = array_combine($values, $values);
        }
        if ( isset($attributes['id']) ) {
            $ids = $attributes['id'];
            unset($attributes['id']);
        }
        $index = 0;
        foreach ( $values as $value => $display ) {
            $html .= "<input type='radio' ";
            $html .= $this->_parse_attributes($attributes, $binding, $options);
            $label_for = '';
            if ( isset($ids[$index]) ) {
                $html .= "id='" . strtolower($ids[$index]) . "' ";
                $label_for = " for='" . strtolower($ids[$index]) . "'";
            }
            $html .= "value='" . $this->escape($value) . "' ";
            if (
                !is_null($binding)
                && isset($attributes['name'])
                && property_exists($binding, $attributes['name'])
                && $binding->$attributes['name'] == $value
            ) {
                $html .= "checked='checked' ";
            }
            $html .= "/> ";
            $html .= "<label" . $label_for;
            $html .= ">" . $display . "</label>";
            $index++;
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
    * @param array $options             The options with which to populate the
    *                                   dropdown list.
    *                                   Must be in the format of:
    *                                   'value' => 'display'
    *                                   (option value will be set to 'value',
    *                                   and the text within the option will be
    *                                   set to 'display')
    * @param obj $binding               The object to which the value of the
    *                                   dropdown list should be mapped.
    * @access public
    * @return string
    */
    public function select($attributes, $options = array(), $binding = null) {
        $element_options = array('is_select' => true);
        $html = "<select ";
        $html .= $this->_parse_attributes($attributes, $binding, $element_options);
        $html = rtrim($html) . ">";
        // Array is not associative
        if ( $options === array_values($options) ) {
            $options = array_combine($options, $options);
        }
        foreach( $options as $value => $display ) {
            $html .= "<option value='" . $this->escape($value) . "' ";

            if (
                !is_null($binding)
                && isset($attributes['name'])
                && property_exists($binding, $attributes['name'])
                && $binding->$attributes['name'] == $value
            ) {
                $html .= "selected='selected' ";
            }
            $html = rtrim($html) . ">" . $this->escape($display) . "</option>";
        }
        $html .= "</select>";

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
        if ( isset($attributes['value']) ) {
            $value = $attributes['value'];
            unset($attributes['value']);
        }

        $options = array('is_textarea' => true);
        $html = "<textarea ";
        $html .= $this->_parse_attributes($attributes, $binding, $options);
        $html = rtrim($html) . '>';
        if ( isset($value) ) {
            $html .= $this->escape($value);
        } elseif (
            !is_null($binding)
            && isset($attributes['name'])
            && property_exists($binding, $attributes['name'])
        ) {
            $html .= $this->escape($binding->$attributes['name']);
        }
        $html .= "</textarea>";

        return $html;
    }

}

?>
