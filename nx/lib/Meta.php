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
 *  The `Meta` class is used to return information
 *  about objects by way of reflection.
 *
 *  @package lib
 */
class Meta {

   /** 
    *  Returns the classname without the namespace. 
    *
    *  @param object|string $obj          The object or class name from which to retrieve the classname.
    *  @access public
    *  @return string
    */
    public static function classname_only($obj) {
        if ( !is_object($obj) && !is_string($obj) ) {
            return false;
        }
        
        $class = explode('\\', (is_string($obj) ? $obj : get_class($obj)));
        return array_pop($class);
    }

   /** 
    *  Returns all of the columns (protected properties that are 
    *  not prefixed with an underscore) of a given object.
    *
    *  @param object $obj                 The object from which to retrieve the properties.
    *  @access public
    *  @return array
    */
    public static function get_columns($obj) {
        $reflection = new \ReflectionClass($obj);
        $props = $reflection->getProperties(\ReflectionProperty::IS_PROTECTED);
        $collection = array();
        foreach ( $props as $prop ) {
            $name = $prop->getName();
            if ( strpos($name, '_') !== 0 ) {
                $collection[$name] = $obj->$name;
            }
        }
        return $collection;
    }

   /** 
    *  Returns all of the protected methods in a given class.
    *
    *  @param object $obj                 The object from which to retrieve the methods.
    *  @access public
    *  @return array
    */
    public static function get_protected_methods($obj) {
        $reflection = new \ReflectionClass($obj);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PROTECTED);
        $collection = array();
        foreach ( $methods as $method ) {
            $collection[] = $method->getName();
        }
        return $collection;
    }

}

?>
