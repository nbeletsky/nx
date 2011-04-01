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
                     BASE_INSTALL."/plugin".PATH_SEPARATOR.
                     BASE_INSTALL."/test/temp");

    ini_set('display_errors', 1);
    date_default_timezone_set('America/Los_Angeles');

    function file_exists_in_include_path($file)
    {
        if ( file_exists($file) ) 
        {
            return realpath($file);
        }

        $paths = explode(PATH_SEPARATOR, get_include_path());

        foreach ($paths as $path) 
        {
            $fullpath = $path . DIRECTORY_SEPARATOR . $file;

            if ( file_exists($fullpath) ) 
            {
                return realpath($fullpath);
            }
        }

        return false;
    }

    function autoload($class) 
    {
        $file = str_replace("\\", "/", $class) . ".php";
        if ( file_exists_in_include_path($file) )
        {
            require_once $file;
        }
        else
        {
            // TODO: Throw exception!
        }
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
