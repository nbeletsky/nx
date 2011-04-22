<?php

namespace nx\lib;

class Debug {

    public static function inspect($msg) {
        echo '<pre>' . get_called_class() . ': ';
        print_r($msg);
        echo '</pre>
        ';
    }
    
}

?>
