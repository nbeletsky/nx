<?
namespace core;

class Controller
{
    public $protected            = null; // array of blacklist protected actions
    public $protected_exceptions = null; // array of whitelist unprotected actions)
    public $handler              = null; // handler for errors, array('controller'=>foo, 'action'=>bar)

    protected $_data = array();

    public function call($action)
    {
        $this->_data = $_REQUEST["data"];

        try
        {
            if ( $this->is_protected($action) )
            {
                $this->protect($action); // should throw an exception
            }
            
            if ( method_exists($this, $action) )
            {
                $this->preload($action);
                
                $assignments = $this->$action();
            }   
            
            foreach($assignments as $name=>$value)
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
    
    public function render($query_string)
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
        $controller_obj->call($action);
    }
}

?>
