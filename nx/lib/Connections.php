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
 *  The `Connections` class contains methods that assist
 *  in accessing database and cache connections.
 *
 *  @package lib
 */
class Connections {

   /**
    *  The collection of cache handlers.
    *
    *  @var array
    *  @access protected
    */
    protected static $_cache = array();

   /**
    *  The collection of database handlers.
    *
    *  @var array
    *  @access protected
    */
    protected static $_db = array();

   /**
    *  The initialization status of the handlers.
    *
    *  @var array
    *  @access protected
    */
    protected static $_initialized = array(
        'cache' => array(),
        'db'    => array()
    );

   /**
    *  The options for handlers.
    *
    *  @var array
    *  @access protected
    */
    protected static $_options = array(
        'cache' => array(),
        'db'    => array()
    );

   /**
    *  Stores the cache connection details using the defined options.
    *
    *  @see app\config\bootstrap\cache.php
    *  @param string $name          The name of the cache handler.
    *  @param array $options        The cache options.  Takes the following
    *                               parameters:
    *                               `plugin`        - The name of the cache plugin.
    *                               `host`          - The hostname of the server
    *                                                 where the cache resides.
    *                               `persistent_id` - A unique ID used to allow
    *                                                 persistence between requests.
    *  @access public
    *  @return void
    */
    public static function add_cache($name, array $options = array()) {
        self::$_options['cache'][$name] = $options;
        self::$_initialized['cache'][$name] = false;
    }

   /**
    *  Stores the database connection details using the defined options.
    *
    *  @see app\config\bootstrap\db.php
    *  @param string $name          The name of the database handler.
    *  @param array $options        The database options.  Takes the
    *                               following parameters:
    *                               `plugin`   - The name of the database plugin.
    *                               `database` - The name of the database.
    *                               `host`     - The database host.
    *                               `username` - The database username.
    *                               `password` - The database password.
    *  @access public
    *  @return void
    */
    public static function add_db($name, array $options = array()) {
        self::$_options['db'][$name] = $options;
        self::$_initialized['db'][$name] = false;
    }

   /**
    *  Returns the cache handler.
    *
    *  @param string $name          The name of the cache handler.
    *  @access public
    *  @return object
    */
    public static function get_cache($name) {
        if ( !self::$_initialized['cache'][$name] ) {
            $plugin = self::$_options['cache'][$name]['plugin'];
            $cache = 'nx\plugin\cache\\' . $plugin;
            unset(self::$_options['cache'][$name]['plugin']);
            self::$_cache[$name] = new $cache(self::$_options['cache'][$name]);
            self::$_initialized['cache'][$name] = true;
        }

        return self::$_cache[$name];
    }

   /**
    *  Returns the database handler.
    *
    *  @param string $name          The name of the database handler.
    *  @access public
    *  @return object
    */
    public static function get_db($name) {
        if ( !self::$_initialized['db'][$name] ) {
            $plugin = self::$_options['db'][$name]['plugin'];
            $db = 'nx\plugin\db\\' . $plugin;
            unset(self::$_options['db'][$name]['plugin']);
            self::$_db[$name] = new $db(self::$_options['db'][$name]);
            self::$_initialized['db'][$name] = true;
        }

        return self::$_db[$name];
    }
}

?>
