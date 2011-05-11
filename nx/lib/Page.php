<?php

namespace nx\lib;

use nx\lib\File;
use nx\lib\Form;

class Page {

    protected static $_controller_location = 'app\controller\\';

   /**
    *  Renders a page.
    *
    *  @param string $query_string        The query string from the url.
    *  @param array $additional           Any additional variables that should be passed to the view.
    *  @access public
    *  @return array
    */
    public static function render($query_string, $additional = array()) {
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
            "^/([A-Za-z0-9\-]+)/?$"=>"/index.php?controller=$1",
            "^/([A-Za-z0-9\-]+)/([\d]+)/?$"=>"/index.php?controller=$1&id=$2",
            "^/([A-Za-z0-9\-]+)\?(.+)$"=>"/index.php?controller=$1&args=$2",
            "^/([A-Za-z0-9\-]+)/([A-Za-z0-9\-_]+)/?$"=>"/index.php?controller=$1&action=$2",
            "^/([A-Za-z0-9\-]+)/([\d]+)\?(.+)$"=>"/index.php?controller=$1&id=$2&args=$3",
            "^/([A-Za-z0-9\-]+)/([A-Za-z0-9\-_]+)/([\d]+)/?$"=>"/index.php?controller=$1&action=$2&id=$3",
            "^/([A-Za-z0-9\-]+)/([A-Za-z0-9\-_]+)\?(.+)$"=>"/index.php?controller=$1&action=$2&args=$3",
            "^/([A-Za-z0-9\-]+)/([A-Za-z0-9\-_]+)/([\d]+)\?(.+)$"=>"/index.php?controller=$1&action=$2&id=$3&args=$4"
        )
        */

        parse_str($query_string, $query);

        $controller = ( isset($query['controller']) ) ? ucfirst($query['controller']) : DEFAULT_CONTROLLER;
        $action =     ( isset($query['action']) )     ? $query['action']              : DEFAULT_ACTION;
        $id =         ( isset($query['id']) )         ? $query['id']                  : null;

        $get = array();
        if ( isset($query['args']) ) {
            $args = substr($query_string, strpos($query_string, $query['args']));
            parse_str($args, $get);
        }

        $whitelist = File::get_filenames_within(ROOT_DIR . '/app/controller');
        $strip_ext = create_function('$val', 'return basename($val, ".php");');
        $whitelist = array_map($strip_ext, $whitelist);

        if ( in_array($controller, $whitelist) ) {
            $controller = self::$_controller_location . $controller; 
            $controller_obj = new $controller(array(
                'http_get'  => $get,
                'http_post' => ( !empty($_POST) ) ? Data::extract_post($_POST) : array()
            ));
            $controller_obj->call($action, $id, $additional);
        } else {
            self::throw_404(DEFAULT_TEMPLATE);
        }

    }

   /**
    *  Renders a 404 page.
    *
    *  @param string $template            The view template to use.
    *  @access public
    *  @return void
    */
    public static function throw_404($template) {
        $view_file = '../view/' . $template . '/404' . VIEW_EXTENSION;
        if ( file_exists($view_file) ) {
            include $view_file;
        }
    }

}

?>
