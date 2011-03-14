<?php
namespace lib;

class Meta 
{
   /** 
    *  Returns the classname without the namespace. 
    *
    *  @param object  $obj    Object from which to retrieve name.
    *  @return string
    */
    public function classname_only($obj)
    {
        if ( !is_object($obj) ) 
        {
            return false;
        }

        $class = new \ReflectionClass($obj);
        return $class->getShortName();
    }

    public function get_private_vars($obj)
    {
        $reflection = new \ReflectionClass($obj);
        $props = $reflection->getProperties(\ReflectionProperty::IS_PROTECTED);
        $properties = array();
        foreach ( $props as $prop )
        {
            // Exclude the variables with a leading underscore
            $name = $prop->getName();
            if ( strpos($name, '_') !== 0 )
            {
                $prop->setAccessible(true);
                $properties[$name] = $prop->getValue($obj);
            }
        }
        return $properties;
    }

}

?>
