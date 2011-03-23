<?php
namespace core;

class View
{
    public function output($filename, $vars, $create_snapshot=false)
    {
        if ( is_array($vars) )
        {
            foreach( $vars as $name=>$value )
            {
                $$name = $value;
            }
        }
        
        if ( file_exists($filename) )
        {
            include($filename);

            if ( $create_snapshot )
            {
                $snapshot = ob_get_contents(); 
                $file = new lib\File(); 
                // TODO: Modify create_snapshot so that the filenames are better handled
                $file->create_snapshot($snapshot, 'html');
            }
        }

    }
}
