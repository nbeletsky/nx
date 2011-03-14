<?

class ValidationServiceAudit extends ValidationService
{   
    protected $example_keychain= array();
    
    public function validate_Example($example)
    {
        $this->set_message((strlen($example->name) == 0), $example, 'name', 'Please provide a name.');
        $this->set_message((strlen($example->address) == 0), $example, 'address', 'Please provide an address.');
        $this->set_message((strlen($example->city) == 0), $example, 'city', 'Please provide a city.');
        $this->set_message((strlen($example->state) == 0), $example, 'state', 'Please select a state.');
        $this->set_message((!preg_match("/^[0-9]{5}$/", $example->zip)), $site, 'zip', 'Please provide a valid, 5-digit zip code.');
            
        return $this->messages;        
    }
    
    function clear_example_keychain()
    {
        $this->example_keychain= array();
        core\Session::set('example_keychain', $this->example_keychain);
    }
    
    function add_example_to_keychain($example_object)
    {
        $this->example_keychain= core\Session::get('example_keychain');
        
        $found= false;
        if (is_array($this->example_keychain))
            foreach($this->example_keychain as $k=>$cached_example)
            {
                if ($example_object->id == $cached_example->id and $example_object::classname() == $cached_example::classname())
                {
                    $this->example_keychain[$k]= $example_object;
                    $found= true;
                    break;
                }
            }
        
        if (!$found)
            $this->example_keychain[]= $example_object;
        
        core\Session::set('example_keychain', $this->example_keychain);
    }
    
    function validate_Examples($obj, $run_meta=true)
    {
        $this->add_example_to_keychain($obj);

        if ($run_meta)
            $this->validate_ExampleMeta($this->example_keychain);
        
        return $this->messages; 
    }

}

?>
