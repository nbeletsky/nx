<?php

class Test extends core\Controller
{

    public function index()
    {   
        $path = realpath(VPU_TEST_DIRECTORY); 	
        if ( !is_dir($path) ) 
        {
            die('The supplied VPU_TEST_DIRECTORY (' . VPU_TEST_DIRECTORY . ') is not a valid directory.  Check your configuration settings and try again.');
        } 

        chdir($path);

        $vpu = new \plugins\test\VPU($path);

        if ( VPU_SANDBOX_ERRORS )
        {
            set_error_handler(array($vpu, 'handle_errors'));
        }

        $suites = $vpu->run();
        $sandbox = $vpu->get_sandbox();
        $this->_create_snapshot = VPU_CREATE_SNAPSHOTS;

        return array('suites'       => $suites,
                     'sandbox'      => $sandbox,
                     'query_string' => 'controller=' . $this->_classname . '&action=suite');
    }

    public function suite()
    {
        return array('query_string' => 'controller=' . $this->_classname . '&action=test');
    }

    public function test()
    {
        return array();
    }
        
}
