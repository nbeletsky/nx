<?php

    // Default base install is the directory above where this config file is stored
    define("BASE_INSTALL", realpath(__DIR__ . '/..')); 

    set_include_path(get_include_path().PATH_SEPARATOR.
                     BASE_INSTALL.PATH_SEPARATOR.
                     BASE_INSTALL."/model".PATH_SEPARATOR.
                     BASE_INSTALL."/controller".PATH_SEPARATOR.
                     BASE_INSTALL."/view".PATH_SEPARATOR.
                     BASE_INSTALL."/core".PATH_SEPARATOR.
                     BASE_INSTALL."/lib".PATH_SEPARATOR.
                     BASE_INSTALL."/plugins".PATH_SEPARATOR.
                     BASE_INSTALL."/test/temp");

    ini_set('display_errors', 1);
    date_default_timezone_set('America/Los_Angeles');

    function autoload($class_name) 
    {
        include_once str_replace("\\", "/", $class_name) . ".php";
    }

    spl_autoload_register('autoload');

    define("DEFAULT_CONTROLLER", "Dashboard");
    define("DEFAULT_ACTION", "index");
    define("DEFAULT_TEMPLATE", "default");

    define("PRIMARY_KEY", "id");
    define("PK_SEPARATOR", "_");
    define("HABTM_SEPARATOR", "__");

    define("VIEW_EXTENSION", ".html");

    define("DEBUG_LEVEL", 1);

    require "config.application.php";
?>
