<?php

namespace nx\lib;

class Connections {

    protected static $_cache = array();
    protected static $_db = array();

    public static function add_cache($name, array $options = array()) {
        $cache = 'nx\plugin\cache\\' . $options['plugin'];
        unset($options['plugin']);
        self::$_cache[$name] = new $cache($options);
    }

    public static function get_cache($name) {
        return self::$_cache[$name];
    }

    public static function add_db($name, array $options = array()) {
        $db = 'nx\plugin\db\\' . $options['plugin'];
        unset($options['plugin']);
        self::$_db[$name] = new $db($options);
    }

    public static function get_db($name) {
        return self::$_db[$name];
    }
}

?>
