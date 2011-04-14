<?php

namespace lib;

class Meta {
   /** 
    *  Returns the classname without the namespace. 
    *
    *  @param object  $obj    Object from which to retrieve name.
    *  @return string
    */
    public static function classname_only($obj) {
        if ( !is_object($obj) ) {
            return false;
        }

        $class = new \ReflectionClass($obj);
        return $class->getShortName();
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
