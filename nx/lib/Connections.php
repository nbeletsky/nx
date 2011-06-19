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
    *  Creates a new cache connection using the defined options.
    *
    *  @see app\config\bootstrap\cache.php
    *  @param string $name          The name of the cache handler.
    *  @param array $options        The cache options.  Takes the
    *                               following parameters:
    *                               `plugin`        - The name of the cache plugin.
    *                               `host`          - The hostname of the server
    *                                                 where the cache resides.
    *                               `persistent_id` - A unique ID used to allow
    *                                                 persistence between requests.
    *  @access public
    *  @return void
    */
    public static function add_cache($name, array $options = array()) {
        $cache = 'nx\plugin\cache\\' . $options['plugin'];
        unset($options['plugin']);
        self::$_cache[$name] = new $cache($options);
    }

   /**
    *  Creates a new database connection using the defined options.
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
        $db = 'nx\plugin\db\\' . $options['plugin'];
        unset($options['plugin']);
        self::$_db[$name] = new $db($options);
    }

   /**
    *  Returns the cache handler.
    *
    *  @param string $name          The name of the cache handler.
    *  @access public
    *  @return object
    */
    public static function get_cache($name) {
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
        return self::$_db[$name];
    }
}

?>
