<?php
namespace lib;

class Debug
{
    public function inspect($level, $msg)
    {
        if ($level <= DEBUG_LEVEL)
        {
            echo("<pre>" . get_called_class() . "($level): ");
            print_r($msg);
            echo "</pre>
            ";
        }
    }
    
}

?>
