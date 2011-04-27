<?php

namespace nx\lib;

class Debug {

   /**
    *  Displays debugging information about a supplied resource.
    *
    *  @param mixed $res          The resource.
    *  @access public
    *  @return void
    */
    public static function inspect($res) {
        echo '<pre>' . get_called_class() . ': ';
        print_r($res);
        echo '</pre>
        ';
    }
    
}

?>
