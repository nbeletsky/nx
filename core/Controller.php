<?
namespace core;

class Controller
{
    public $protected            = null; // array of blacklist protected actions
    public $protected_exceptions = null; // array of whitelist unprotected actions
    public $handler              = null; // handler for errors, array('controller'=>foo, 'action'=>bar)

    protected $_http_get = array();
    protected $_http_post = array();

    public function call($action, $id=null)
    {
        try
        {
            if ( $this->is_protected($action) )
            {
                $this->protect($action); // should throw an exception
            }
            
            if ( method_exists($this, $action) )
            {
                $this->preload($action);
                
                $assignments = $this->$action($id);
            }   
            
            if ( is_array($assignments) )
            {
                foreach( $assignments as $name=>$value )
                {
                    $$name = $value;
                }
            }
            
            $view_action = "../view/" . get_class($this) . "/" . $action . VIEW_EXTENSION;
            if ( file_exists($view_action) )
            {
                include($view_action);
            }
        }
        // TODO: Fix exceptions
        catch (\Exception $e)
        {
            if ( $this->handler )
            {
                render("/" . $this->handler['controller'] . "/" . $this->handler['action'] . "/" .$this->id);
            }
            
            print_r("<pre>" . $e->getMessage() . $e->getTraceAsString() . '</pre>');
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
        exit;
    }
    
    public function render($query_string)
    {
        // URL layout
        // foobar.com/
        // foobar.com/controller
        // foobar.com/controller/id
        // foobar.com/controller/action/id
        // foobar.com/controller/action/id/args
        /* 
        url.rewrite-once = (
            "^/$"=>"/public/index.php",
            "^/([A-Za-z0-9\.\-]+)$"=>"/public/index.php?controller=$1",
            "^/([A-Za-z0-9\.\-]+)/([A-Za-z0-9\.\-]+)$"=>"/public/index.php?controller=$1&$id=$2",
            "^/([A-Za-z0-9\.\-]+)/([A-Za-z0-9\.\-]+)/([A-Za-z0-9\.\-]+)$"=>"/public/index.php?controller=$1&action=$2&id=$3",
            "^/([A-Za-z0-9\.\-]+)/([A-Za-z0-9\.\-]+)/([A-Za-z0-9\.\-]+)/([A-Za-z0-9\.\-\&\=\_]+)$"=>"/public/index.php?controller=$1&action=$2&id=$3&args=$4"
        )
        */

        parse_str($query_string, $query);

        $controller = ( isset($query['controller']) ) ? $query['controller'] : DEFAULT_CONTROLLER;
        $action =     ( isset($query['action']) )     ? $query['action']     : DEFAULT_ACTION;
        $id =         ( isset($query['id']) )         ? $query['id']         : null;

        if ( isset($query['args']) )
        {
            $args = substr($query_string, strpos($query['args']));
            parse_str($args, $this->_http_get);
        }
        else
        {
            $this->_http_get = array();
        }

        if ( isset($_POST) )
        {
            $this->_http_post = $_POST;
        }

        $file = new \lib\File();
        $whitelist = $file->get_filenames_within(BASE_INSTALL . '/controller');
        $strip_ext = create_function('$val', 'return basename($val, ".php");');
        $whitelist = array_map($strip_ext, $whitelist);

        // TODO: Load user's template
        //$template = DEFAULT_TEMPLATE;
        //$page = 
        //include "../view/" . $template . '/' . $page . VIEW_EXTENSION;
        if ( in_array($controller, $whitelist) )
        {
            $controller_obj = new $controller();
            $controller_obj->call($action, $id);
        }
        else
        {
            // TODO: Throw exception!
        }
    }
}

?>
