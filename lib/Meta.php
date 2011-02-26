<?php
namespace core;

class Meta 
{
   /** 
    *  Returns the classname without the namespace. 
    *
    *  @param object|string  $obj    Object or classname from which to retrieve name 
    *  @return string
    */
    public function classname_only($obj)
    {
        if (!is_object($obj) && !is_string($obj)) {
            return false;
        }
        
        $class = explode('\\', (is_string($obj) ? $obj : get_class($obj)));
        return $class[count($class) - 1];
    }

}

?>
