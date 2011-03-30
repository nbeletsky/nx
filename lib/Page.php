<?php
namespace lib;

class Page
{
    public function render($query_string, $additional=null)
    {
        // URL layout
        // foobar.com/
        // foobar.com/controller
        // foobar.com/controller/id
        // foobar.com/controller/action/id
        // foobar.com/controller/action/id/args
        /* 
        server.document-root = "/srv/http/YOURSITE/public"
        url.rewrite-once = (
            "^/$"=>"/index.php",
            "^/([A-Za-z0-9\-]+)$"=>"/index.php?controller=$1",
            "^/([A-Za-z0-9\-]+)/([A-Za-z0-9\-]+)$"=>"/index.php?controller=$1&$id=$2",
            "^/([A-Za-z0-9\-]+)/([A-Za-z0-9\-]+)/([A-Za-z0-9\-]+)$"=>"/index.php?controller=$1&action=$2&id=$3",
            "^/([A-Za-z0-9\-]+)/([A-Za-z0-9\-]+)/([A-Za-z0-9\-]+)/([A-Za-z0-9\-\&\=\_]+)$"=>"/index.php?controller=$1&action=$2&id=$3&args=$4"
        )
        */

        parse_str($query_string, $query);

        $controller = ( isset($query['controller']) ) ? ucfirst($query['controller']) : DEFAULT_CONTROLLER;
        $action =     ( isset($query['action']) )     ? $query['action']     : DEFAULT_ACTION;
        $id =         ( isset($query['id']) )         ? $query['id']         : null;

        $get = array();
        if ( isset($query['args']) )
        {
            $args = substr($query_string, strpos($query['args']));
            parse_str($args, $get);
        }

        $file = new \lib\File();
        $whitelist = $file->get_filenames_within(BASE_INSTALL . '/controller');
        $strip_ext = create_function('$val', 'return basename($val, ".php");');
        $whitelist = array_map($strip_ext, $whitelist);

        if ( in_array($controller, $whitelist) )
        {
            $controller_obj = new $controller($get, $_POST);
            $controller_obj->call($action, $id, $additional);
        }
        else
        {
            // TODO: Throw exception!
        }
    }

}

?>
