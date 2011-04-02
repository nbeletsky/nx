<?php
namespace lib;

class Data
{

    public function extract_post($data)
    {
        $collection = array();
        foreach ( $data as $child_key=>$child )
        {
            if ( is_array($child) )
            {
                foreach ( $child as $id=>$grandchild_array )
                {
                    $obj = new $child_key($id);
                    foreach ( $grandchild_array as $name=>$val )
                    {
                        $type = substr($name, strrpos($name, '|') + 1);
                        $obj->$name = $this->sanitize($val, $type);
                    }
                    $collection[$classname][$id] = $obj; 
                }
            }
            else
            {
                $type_loc = strrpos($child_key, '|');
                $type = substr($child_key, $type_loc + 1);
                $id = substr($child_key, 0, $type_loc);
                $collection[$id] = $this->sanitize($child, $type);
            }
        }

        return $collection; 
    }

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
                $data = trim(strval(filter_var($data, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES)));
                break;
        }
        return $data;
    }

}

?>
