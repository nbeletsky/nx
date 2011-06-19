<?php

/**
 * NX
 *
 * @author    Nick Sinopoli <NSinopoli@gmail.com>
 * @copyright Copyright (c) 2011, Nick Sinopoli
 * @license   http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace nx\lib;

use nx\lib\File;
use nx\core\View;

/*
 *  The `Dispatcher` class is used to handle url routing and
 *  page rendering.
 *
 *  @package lib
 */
class Dispatcher {

   /**
    *  Parses a query string and returns the controller, action,
    *  id, and any additional arguments passed via $_GET.
    *
    *  @param string $query_string        The controller name.
    *  @access public
    *  @return array
    */
    public static function parse_query_string($query_string) {

        parse_str($query_string, $query);

        $controller = ( isset($query['controller']) )
            ? ucfirst($query['controller'])
            : 'Dashboard';
        $action = ( isset($query['action']) )
            ? $query['action']
            : 'index';
        $id = ( isset($query['id']) )
            ? $query['id']
            : null;

        $get = array();
        if ( isset($query['args']) && $query['args'] != '' ) {
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
    *  @access public
    *  @return bool
    */
    public static function render($args) {
        $controller_name = 'app\controller\\' . $args['controller'];

        if ( !class_exists($controller_name) ) {
            self::throw_404('default');
            return false;
        }

        $controller = new $controller_name(array(
            'http_get'  => $args['get'],
            'http_post' => $args['post']
        ));

        $results = $controller->call($args['action'], $args['id']);
        if ( !is_array($results) ) {
            self::throw_404($controller->get_template());
            return false;
        }

        // AJAX
        if ( is_string($results['vars']) ) {
            echo htmlspecialchars($results['vars'], ENT_QUOTES, 'UTF-8');
            return true;
        }

        $view = new View();
        return $view->render($results['file'], $results['vars']);
    }

   /**
    *  Renders a 404 page.
    *
    *  @param string $template            The view template to use.
    *  @access public
    *  @return void
    */
    public static function throw_404($template) {
        require 'app/view/' . $template . '/404.html';
    }

}

?>
