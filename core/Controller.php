<?php
namespace core;

class Controller
{
    protected $_protected            = null; // array of blacklist protected actions
    protected $_protected_exceptions = null; // array of whitelist unprotected actions
    protected $_handler              = null; // handler for errors, array('controller'=>foo, 'action'=>bar)

    protected $_http_get = array();
    protected $_http_post = array();

    protected $_validation_errors = array();

    protected $_template = DEFAULT_TEMPLATE; 
    protected $_create_snapshot = false; 
    protected $_classname = null;

    protected $_token = null;

    public function __construct($get=null, $post=null)
    {
        $data = new \lib\Data();
        $this->_http_get = ( !is_null($get) ) ? $get : array();
        $this->_http_post = ( isset($post) ) ? $data->extract_post($post) : array();

        $this->_classname = get_called_class(); 
    }

    public function call($action, $id=null, $additional=null)
    {
        try
        {
            if ( $this->is_protected($action) )
            {
                $this->protect($action); // should throw an exception
            }
            
            if ( !method_exists($this, $action) )
            {
                // TODO: throw 404!
                die();
            }   

            $this->_validate($action);

            $this->preload($action);
            
            $to_view = $this->$action($id);

            // AJAX
            if ( is_string($to_view) )
            {
                echo $to_view;
                exit;
            }
            
            $view_file = "../view/" . $this->_template . '/' . get_class($this) . "/" . $action . VIEW_EXTENSION;

            // TODO: Fix this logic
            if ( is_array($additional) )
            {
                if ( !is_array($to_view) )
                {
                    $to_view = $additional;
                }
                else
                {
                    $to_view = array_merge($to_view, $additional);
                }
            }

            $libs = ( method_exists($this, '_load_libraries') ) ? $this->_load_libraries() : null;

            if ( is_array($libs) )
            {
                if ( !is_array($to_view) )
                {
                    $to_view = $libs;
                }
                else
                {
                    $to_view = array_merge($to_view, $libs);
                }
            }

            if ( is_array($to_view) )
            {
                foreach( $to_view as $NAME_FOR_VIEWS=>$value )
                {
                    $$NAME_FOR_VIEWS = $value;
                }
            }
            
            if ( file_exists($view_file) )
            {
                include($view_file);

                if ( $this->_create_snapshot )
                {
                    $snapshot = ob_get_contents(); 
                    $file = new lib\File(); 
                    $file->create_snapshot($snapshot, basename(realpath($view_file)));
                }
            }

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
        // preload some stuff
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

    private function _validate($action)
    {
        if ( !empty($this->_http_post) )
        {
            if ( $this->_http_post['token'] !== $_SESSION[$this->_classname . '_token'] )
            {
                // CSRF attack
                die('CSRF detected!');
            }

            $validator = '\\lib\validators\\' . $this->_classname; 
            if ( class_exists($validator) )
            {
                $validator = new $validator($this->_http_post);
                $this->_validation_errors = $validator->$action();
            }
        }

        $this->_token = sha1(microtime() . CSRF_TOKEN_SALT);
        $_SESSION[$this->_classname . '_token'] = $this->_token;

    }
    
}

?>
