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

}

?>
