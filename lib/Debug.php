<?php
namespace core;    

class Debug
{
    public function inspect($level, $msg)
    {
        if ($level <= DEBUG_LEVEL)
        {
            print_r("<pre>" . get_called_class() . "($level): " . $msg . "</pre>");
        }
    }
    
}

?>
