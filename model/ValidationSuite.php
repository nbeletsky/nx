<?php

// TODO: Fix class
class ValidationSuite extends ApplicationModel
{   
    
    static function for_whatever()
    {
        // Loop over pages 
        // foreach ( )
        // {
        //    ValidationSuite::for_page($k, $something, $something_else);
        //
        // }
    }

    // this is run via AJAX
    static function for_change($page, $obj, $classname, $id, $field, $value)
    {
        if (!$field)
            return;
            
        $vs= new ValidationSuite();
        // get the object out of the cache:
        $cache= $vs->get_object_cache();
        $object= $cache[$classname.'_'.$id];
        
        // update the field that was changed to the new value
        //  prior to retrunning the validators:
        $object->$field= $value;
        
        // store the change only in the local cache
        //  since they didn't hit a save() to store permanently:
        $vs->set_object_cache($object); 
        
        // reload the service and inject the updated object into
        //  the validation system:
        $validator= "validate_$classname";
        
        // load out the messages by page and update with the 
        //  results of validation:
        $messages= $vs->get_message_cache();
        if (method_exists($service, $validator))
            $messages[$page]= $service->$validator($object);
        $vs->set_message_cache($messages);
        
        // send the messages back up to the page:
        return $messages[$page];
    }
    
    // update page, object and message caches with the results of running $this->validate_page_$page()
    static function for_page($page)
    {
        $vs= new ValidationSuite();
        $vs->set_message_cache(array());
        $method= "validate_page_".$page;
        if (method_exists($vs, $method))
        {
            //$messages= $vs->get_message_cache();
            if (!is_array($messages[$page])) $messages[$page] = array();
            $service= $vs->get_service();
            $service_messages= $vs->$method($service);
            if (is_array($service_messages))
                $messages[$page]= array_merge($messages[$page], $service_messages);

            $vs->set_message_cache($messages);

            return $service_messages;
        }
    }
    
    static function get_messages($page)
    {
        $messages= ValidationSuite::get_message_cache();
        // clean up the messages:
        $messages[$page] = array_filter($messages[$page], function($val) { return $val != 'null'; });
        return $messages[$page];
    }
    
    // PRIMARY ENTRY POINT FOR OVERVIEWS
    static function is_page_valid($page)
    {
        $vs= new ValidationSuite();
                
        ValidationSuite::for_page($page);

        $is_valid= true;
        
        $messages= $vs->get_message_cache();
        
        if (is_array($messages[$page]))
        {
            $messages[$page] = array_filter($messages[$page], function($val) { return $val != 'null'; });
            $is_valid = ( count($messages[$page]) < 1 );
        }
        
        return $is_valid;
    }
    
    function set_message_cache($messages)
    {
        core\Session::set('validation_cache', $messages);
    }
    
    function get_message_cache()
    {
        $cache= core\Session::get('validation_cache');
        if (!is_array($cache))
            $cache= array();
        return $cache;
    }
    
    function set_object_cache($object)
    {
        $o= core\Session::get('val_object_cache');
        if (!method_exists($object, "classname"))
        {
            exit();
            //stack_trace();
        }
        $o[classname_only($object::classname()).'_'.$object->id]= $object;
        core\Session::set('val_object_cache', $o);
    }    
    
    function get_object_cache()
    {
        return core\Session::get('val_object_cache');
    }
   
    function validate_page_Example($service)
    {
        $service->clear_example_keychain();
        $messages= array(); 
        foreach($examples as $example)
        {
            $this->set_object_cache($example);
            $cname= "validate_".$example::classname();
            if (method_exists($service, $cname))
            {
                $service->add_example_to_keychain($example);
                $object_messages= $service->$cname($example, false);
                if (is_array($object_messages))
                    $messages= array_merge($messages, $object_messages);
            }
        }
        $object_messages = $service->validate_MetaExamples($examples);
        $messages= array_merge($messages, $object_messages);
        return $messages;
    }
    
}

?>
