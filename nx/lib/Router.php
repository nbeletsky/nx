<?php

/**
 * NX
 *
 * @author    Nick Sinopoli <NSinopoli@gmail.com>
 * @copyright Copyright (c) 2011, Nick Sinopoli
 * @license   http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace nx\lib;

/*
 *  The `Router` class is used to handle url routing.
 *
 *  @package lib
 */
class Router {

   /**
    *  The routing defaults.
    *
    *  @var array
    *  @access protected
    */
    public static $defaults = array(
        'controller' => 'Dashboard',
        'action'     => 'index',
        'id'         => null
    );

   /**
    *  The url rewrite scheme.
    *
    *  @var array
    *  @access protected
    */
    protected static $_routes = array(
        '/^\/?$/'                                                  => '', // this will return our defaults
        '/^\/([A-Za-z0-9\-]+)\/?$/'                                => 'controller=$1',
        '/^\/([A-Za-z0-9\-]+)\?(.+)$/'                             => 'controller=$1&args=$2',

        '/^\/([A-Za-z0-9\-]+)\/([\d]+)\/?$/'                       => 'controller=$1&id=$2',
        '/^\/([A-Za-z0-9\-]+)\/([\d]+)\?(.+)$/'                    => 'controller=$1&id=$2&args=$3',

        '/^\/([A-Za-z0-9\-]+)\/([A-Za-z0-9\-_]+)\/?$/'             => 'controller=$1&action=$2',
        '/^\/([A-Za-z0-9\-]+)\/([A-Za-z0-9\-_]+)\?(.+)$/'          => 'controller=$1&action=$2&args=$3',

        '/^\/([A-Za-z0-9\-]+)\/([A-Za-z0-9\-_]+)\/([\d]+)\/?$/'    => 'controller=$1&action=$2&id=$3',
        '/^\/([A-Za-z0-9\-]+)\/([A-Za-z0-9\-_]+)\/([\d]+)\?(.+)$/' => 'controller=$1&action=$2&id=$3&args=$4'
    );

   /**
    *  Parses a query string and returns the controller, action,
    *  id, and any additional arguments passed via $_GET.
    *
    *  @param string $query_string        The controller name.
    *  @access public
    *  @return array
    */
    protected static function _parse_query_string($query_string) {
        $query = array();
        parse_str($query_string, $query);

        $controller = ( isset($query['controller']) )
            ? ucfirst($query['controller'])
            : self::$defaults['controller'];
        $action = ( isset($query['action']) )
            ? $query['action']
            : self::$defaults['action'];
        $id = ( isset($query['id']) )
            ? $query['id']
            : self::$defaults['id'];

        $get = array();
        if ( isset($query['args']) && $query['args'] != '' ) {
            $args = substr($query_string, strpos($query_string, $query['args']));
            parse_str($args, $get);
        }
        return compact('controller', 'action', 'id', 'get');
    }

   /**
    *  Parses a url in accordance with the defined routes,
    *  and returns the necessary components to route the request.
    *
    *  @see nx\lib\Router::_parse_query_string()
    *  @param string $url                 The url.
    *  @access public
    *  @return array
    */
    public static function parse_url($url) {
        $matches = array();
        foreach ( self::$_routes as $pattern => $route ) {
            if ( preg_match($pattern, $url, $matches) ) {
                unset($matches[0]);
                foreach ( $matches as $key => $match ) {
                    $route = str_replace('$' . $key, $match, $route);
                }
                return self::_parse_query_string($route);
            }
        }
        return false;
    }

}

?>
