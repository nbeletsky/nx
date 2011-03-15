<?php

// TODO: Fix class
class ValidationSuite extends ApplicationModel
{   
    protected $_messages;

    public function validate_page_Example($service)
    {
        $this->set_message((strlen($example->name) == 0), $example, 'name', 'Please provide a name.');
        $this->set_message((strlen($example->address) == 0), $example, 'address', 'Please provide an address.');
        $this->set_message((strlen($example->city) == 0), $example, 'city', 'Please provide a city.');
        $this->set_message((strlen($example->state) == 0), $example, 'state', 'Please select a state.');
        $this->set_message((!preg_match("/^[0-9]{5}$/", $example->zip)), $site, 'zip', 'Please provide a valid, 5-digit zip code.');

        return $this->_messages;        
    }

    public function set_message($boolean_test, $object, $field, $message)
    {
        if ($boolean_test)
        {                
            $this->messages[view_id($object, $field)]= $message;
        }
        else
            $this->unset_message($object, $field, $message);
    }
        
    public function unset_message($object, $field, $message)
    {
        $this->messages[view_id($object, $field)] = 'null';
    }
    
}

?>
