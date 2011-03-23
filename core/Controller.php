<?php
namespace core;

class Controller
{
    protected $_protected            = null; // array of blacklist protected actions
    protected $_protected_exceptions = null; // array of whitelist unprotected actions
    protected $_handler              = null; // handler for errors, array('controller'=>foo, 'action'=>bar)

    protected $_http_get = array();
    protected $_http_post = array();

    protected $_template = null; 
    protected $_create_snapshot = false; 

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
                
                $to_view = $this->$action($id);
            }   

            // AJAX
            if ( is_string($to_view) )
            {
                echo $to_view;
                exit;
            }
            
            $template = ( !is_null($this->_template) ) ? $this->_template : DEFAULT_TEMPLATE;
            $view_file = "../view/" . $template . '/' . get_class($this) . "/" . $action . VIEW_EXTENSION;

            $view = new core\View();
            $view->output($view_file, $to_view, $this->_create_snapshot);
        }
        // TODO: Fix exceptions
        catch (\Exception $e)
        {
            if ( $this->_handler )
            {
                render("/" . $this->_handler['controller'] . "/" . $this->_handler['action'] . "/" .$this->id);
            }
            
            print_r("<pre>" . $e->getMessage() . $e->getTraceAsString() . '</pre>');
            exit();
        }  

    }
    
    public function is_protected($action)
    {
        if ( is_array($this->_protected) )
        {
            return ( in_array($action, $this->_protected) );
        }
            
        if ( is_array($this->_protected_exceptions) )
        {
            return ( in_array($action, $this->_protected_exceptions) );
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

   /**
    *  Provides the redirect location based on the page provided.
    *       
    *  @param string $page      The page to be checked.
    *  @access public
    *  @return string
    */
    public function redirect($page)
    {
        $query = '?' . parse_url($page, PHP_URL_QUERY);
        $page = str_replace($query, '', $page);
        //$redirect_location = $_SERVER['SERVER_NAME'] . '/';
        switch ( $page ) 
        {
            case 'index':
                $redirect_location = 'index.php';
                break;    
            default:
                //$redirect_location .= 'index.php';
                $redirect_location = 'index.php';
                break;    
        }

        if ( headers_sent() )
        {
            echo '<meta content="0; url=' . $redirect_location . '" http-equiv="refresh"/>';
        }
        else
        {
            header("Location: $redirect_location");
        }
        exit();
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
