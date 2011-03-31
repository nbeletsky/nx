<?php
namespace lib;

class Data
{

    public function extract_post($data)
    {
        if ( isset($data['data']) )
        {
            // Trim the first layer
            $data = $data['data'];
        }
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
                        $type = substr($name, strrpos($name, '|') + 1);
                        $obj->$name = $this->sanitize($val, $type);
                    }
                    $collection[$classname][$id] = $obj; 
                }
                else
                {
                    $type_loc = strrpos($id, '|');
                    $type = substr($id, $type_loc + 1);
                    $id = substr($id, 0, $type_loc);
                    $collection[$classname][$id] = $this->sanitize($grandchild_array, $type);
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
            case 'b':
                $data = (bool) filter_var($data, FILTER_SANITIZE_NUMBER_INT);
                break;
            case 'f':
                $data = floatval(filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
                break;
            case 'i':
                $data = intval(filter_var($data, FILTER_SANITIZE_NUMBER_INT));
                break;
            case 's':
                $data = strval(filter_var($data, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES));
                break;
        }
        return $data;
    }

}

?>
