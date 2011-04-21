<?php

namespace nx\lib;

class Meta {
   /** 
    *  Returns the classname without the namespace. 
    *
    *  @param object|string  $object    Object or class name from which to retrieve name.
    *  @return string
    */
    public static function classname_only($object) {
        if ( !is_object($object) && !is_string($object) ) {
            return false;
        }
        
        $class = explode('\\', (is_string($object) ? $object : get_class($object)));
        return array_pop($class);
    }

    public static function get_protected_methods($obj) {
        $reflection = new \ReflectionClass($obj);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PROTECTED);
        $collection = array();
        foreach ( $methods as $method ) {
            $collection[] = $method->getName();
        }
        return $collection;
    }

    public static function get_protected_vars($obj) {
        $reflection = new \ReflectionClass($obj);
        $props = $reflection->getProperties(\ReflectionProperty::IS_PROTECTED);
        $collection = array();
        foreach ( $props as $prop ) {
            // Exclude the variables with a leading underscore
            $name = $prop->getName();
            if ( strpos($name, '_') !== 0 ) {
                $prop->setAccessible(true);
                $collection[$name] = $prop->getValue($obj);
            }
        }
        return $collection;
    }

}

?>
