<?php

abstract class ValidationService 
{   
    protected $messages;
    
    function get_message($object, $field, $messages)
    {
        return $this->messages[view_id($object, $field)];
    }
    
    function set_message($boolean_test, $object, $field, $message)
    {
        if ($boolean_test)
        {                
            $this->messages[view_id($object, $field)]= $message;
        }
        else
            $this->unset_message($object, $field, $message);
    }
        
    function unset_message($object, $field, $message)
    {
        $this->messages[view_id($object, $field)] = 'null';
    }
}

?>
