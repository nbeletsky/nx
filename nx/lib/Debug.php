<?php

/**
 * NX
 *
 * @author    Nick Sinopoli <NSinopoli@gmail.com>
 * @copyright Copyright (c) 2011, Nick Sinopoli
 * @license   http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace nx\lib;

/*
 *  The `Debug` class contains methods that help debug
 *  an application.
 *
 *  @package lib
 */
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
