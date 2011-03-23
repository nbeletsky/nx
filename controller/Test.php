<?php

require 'VPU.php';

class Test extends core\Controller
{

    public function index()
    {   
        // TODO: Fix all VPU-related constants!
        $path = realpath(VPU_TEST_DIRECTORY); 	
        if ( !is_dir($path) ) 
        {
            die('The supplied VPU_TEST_DIRECTORY (' . VPU_TEST_DIRECTORY . ') is not a valid directory.  Check your configuration settings and try again.');
        } 

        chdir($path);

        $vpu = new VPU($path);

        if ( VPU_SANDBOX_ERRORS )
        {
            set_error_handler(array($vpu, 'handle_errors'));
        }

        $results = $vpu->run();

        // TODO: Fix this!
        echo $vpu->to_HTML($results);

        if ( VPU_CREATE_SNAPSHOTS )
        {
            $snapshot = ob_get_contents(); 
            $file = new lib\File(); 
            // TODO: Modify create_snapshot so that the filenames are better handled
            // TODO: Use VPU_SNAPSHOT_DIRECTORY here 
            $file->create_snapshot($snapshot, 'html');
        }
    }
        
}
