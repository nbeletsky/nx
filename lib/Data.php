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
                $obj = new $classname($id);
                foreach ( $grandchild_array as $name=>$val )
                {
                    $obj->$name = $val;
                }
                $collection[] = $obj;
            }
        }

        return $collection; 
    }

}

?>
