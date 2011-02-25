<?
namespace core;

class Controller
{
    public $protected            = null; // array of blacklist protected actions
    public $protected_exceptions = null; // array of whitelist unprotected actions)
    public $handler              = null; // handler for errors, array('controller'=>foo, 'action'=>bar)

    protected $_data = array();

    private $_assignments = array();


    public function assign($var, $value=null)
    {
        if ( is_array($var) && is_null($value) )
        {
            $this->_assignments = array_merge($this->_assignments, $var);
        }
        else
        {
            $this->_assignments[$var]= $value;
        }
    }

    public function call($action, $assignments=null)
    {
        $this->_data = $_REQUEST["data"];

        if ( $assignments )
        {
            $this->_assignments = $assignments;
        }
        
        try
        {
            if ( $this->is_protected($action) )
            {
                $this->protect($action); // should throw an exception
            }
            
            if ( method_exists($this, $action) )
            {
                $this->preload($action);
                
                $this->$action();
            }   
            
            // assignments should be handled after the call check
            //  so that pass-thru assigns can still be assigned downwards.
            foreach($this->_assignments as $name=>$value)
            {
                $$name = $value;
            }
            
            $view_action = "../view/".classname_only(static::classname())."/".$action.VIEW_EXTENSION;
            if (file_exists($view_action))
            {
                include($view_action);
            }
        }
        // TODO: Fix exceptions
        catch (\Exception $e)
        {
            if ($this->handler)
            {
                render("/".$this->handler['controller']."/".$this->handler['action']."/".$this->id);
            }
            
            print_r("Controller caught a message it didn't know how to handle:<br><pre>");
            print_r($e->getMessage());
            print_r($e->getTraceAsString());
            print_r('</pre>');
            exit;
        }  

    }
    
    public function is_protected($action)
    {
        if ( is_array($this->protected) )
        {
            return ( in_array($action, $this->protected) );
        }
            
        if ( is_array($this->protected_exceptions) )
        {
            return ( in_array($action, $this->protected_exceptions) );
        }

        return false;
    }

    public function preload($action)
    {
        // called before call(). override to do stuff before loading $action.
    }
    
    public function protect($action)
    {
        // override to protect things.
    }

    public function redirect($url)
    {
        if ( headers_sent() )
        {
            print '<meta content="0; url='.$url.'" http-equiv="refresh"/>';
        }
        else
        {
            header("Location: $url");
        }
        exit();
    }
    
    public function render($query_string, $assignments=null)
    {
        // TODO: URL layout?
        // foobar.com/controller
        // foobar.com/controller/id
        // foobar.com/controller/action/id
        // foobar.com/controller/action/id/arg
        // foobar.com/controller/action/id/arg[0]/arg[1] - etc...

        parse_str($query_string, $query);

        $controller = ( isset($query["controller"]) ) ? $query["controller"] : DEFAULT_CONTROLLER;
        $action =     ( isset($query["action"]) )     ? $query["action"]     : DEFAULT_ACTION;
        $id =         ( isset($query["id"]) )         ? $query["id"]         : null;

        // TODO: Sanitize the controller!  Blacklist/whitelist?

        // TODO: Load user's template
        //$template = DEFAULT_TEMPLATE;
        //$page = 
        //include "../view/" . $template . '/' . $page . VIEW_EXTENSION;

        $controller_obj = new $controller();
        $controller_obj->call($action, $assignments);
    }
}

?>
