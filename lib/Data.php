<?php
namespace lib;

class Data
{

    public function extract_post($data)
    {
        $collection = array();
        foreach ( $data as $classname=>$child_array )
        {
            foreach ( $child_array as $id=>$grandchild_array )
            {
                if ( is_numeric($id) )
                {
                    $obj = new $classname($id);
                    foreach ( $grandchild_array as $name=>$val )
                    {
                        $obj->$name = $val;
                    }
                    $collection['objects'][$classname . '_' . $id] = $obj;
                }
                else
                {
                    $collection[$classname][$id] = $grandchild_array;
                }
            }
        }

        return $collection; 
    }

    // TODO: Fix this!  Implement sanitizers for POST!
   /**
    *  Sanitizes input according to type.
    * 
    *  @param mixed $data      The data to be sanitized.
    *  @param string $type     The type of validation.
    *  @access public
    *  @return string
    */
    public function sanitize($data, $type) 
    {
        switch ( $type ) 
        {
            case 'float' :
                $data = filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                break;
            case 'int' :
                $data = filter_var($data, FILTER_SANITIZE_NUMBER_INT);
                break;
            case 'string' :
                $data = filter_var($data, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
                break;
        }
        return $data;
    }


}

?>
