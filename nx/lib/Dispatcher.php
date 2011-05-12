<?php

namespace nx\lib;

use nx\lib\File;
use nx\lib\Form;

class Dispatcher {

    protected static $_controller_location = 'app\controller\\';

    public static function is_whitelisted($controller) {
        $whitelist = File::get_filenames_within(ROOT_DIR . '/app/controller');
        $strip_ext = create_function('$val', 'return basename($val, ".php");');
        $whitelist = array_map($strip_ext, $whitelist);
        return in_array($controller, $whitelist);
    }

    public static function parse_query_string($query_string) {

        parse_str($query_string, $query);

        $controller = ( isset($query['controller']) ) ? ucfirst($query['controller']) : DEFAULT_CONTROLLER;
        $action =     ( isset($query['action']) )     ? $query['action']              : DEFAULT_ACTION;
        $id =         ( isset($query['id']) )         ? $query['id']                  : null;

        $get = array();
        if ( isset($query['args']) ) {
            $args = substr($query_string, strpos($query_string, $query['args']));
            parse_str($args, $get);
        }
        return compact('controller', 'action', 'id', 'get');
    }

   /**
    *  Renders a page.
    *
    *  @see nx\lib\Dispatcher::parse_query_string()
    *  @param array $args                 The data parsed from the query string.
    *  @param array $additional           Any additional variables that should be passed to the view.
    *  @access public
    *  @return array
    */
    public static function render($args, $additional = array()) {
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

        if ( !self::is_whitelisted($args['controller']) ) {
            self::throw_404(DEFAULT_TEMPLATE);
        } else {
            $controller_name = self::$_controller_location . $args['controller']; 
            $controller = new $controller_name(array(
                'http_get'  => $args['get'],
                'http_post' => $args['post']
            ));
            $controller->call($args['action'], $args['id'], $additional);
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
