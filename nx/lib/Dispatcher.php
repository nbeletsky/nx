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
 *  The `Dispatcher` class is used to handle url routing and
 *  page rendering.
 *
 *  @package lib
 */
class Dispatcher {

    protected static $_config = array(
        'classes'   => array(
            'request' => 'nx\lib\Request',
            'router'  => 'nx\lib\Router',
            'view'  =>   'nx\core\View'
        )
    );

   /**
    *  Sets the configuration options for the dispatcher.
    *
    *  @param array $config        The configuration options.
    *  @access public
    *  @return void
    */
	public static function config(array $config = array()) {
        static::$_config = $config + static::$_config;
	}

   /**
    *  Renders a page.
    *
    *  @param string $url          The url representing the page to be rendered.
    *  @param bool $is_include     Whether or not the page is an include.
    *  @access public
    *  @return bool
    */
    public static function render($url, $is_include = false) {
        $router = static::$_config['classes']['router'];
        if ( !$args = $router::parse_url($url) ) {
            self::throw_404('web');
            return false;
        }

        $controller_name = 'app\controller\\' . $args['controller'];

        if ( !class_exists($controller_name) ) {
            self::throw_404('web');
            return false;
        }

        $request = static::$_config['classes']['request'];
        $args['post'] = $request::extract_post($_POST);

        $controller = new $controller_name(array(
            'http_get'  => $args['get'],
            'http_post' => $args['post']
        ));

        if ( !$controller->is_accessible() && !$is_include ) {
            self::throw_404($controller->get_template());
            return false;
        }

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

        $view = static::$_config['classes']['view'];
        $view = new $view();
        return $view->render($results['file'], $results['vars']);
    }

   /**
    *  Renders a 404 page.
    *
    *  @param string $template     The view template to use.
    *  @access public
    *  @return void
    */
    public static function throw_404($template) {
        require 'app/view/' . $template . '/404.html';
    }

}

?>
