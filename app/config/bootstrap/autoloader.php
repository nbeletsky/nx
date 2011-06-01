<?php

define('NX_ROOT', dirname(dirname(dirname(__DIR__)))); 
define('CONTROLLER_LOCATION', NX_ROOT . '/app/controller'); 
define('MODEL_LOCATION', NX_ROOT . '/app/model'); 

set_include_path(
    get_include_path()  . PATH_SEPARATOR .
    NX_ROOT             . PATH_SEPARATOR .
    CONTROLLER_LOCATION . PATH_SEPARATOR .
    MODEL_LOCATION      . PATH_SEPARATOR 
);

function file_exists_in_include_path($file) {
    if ( file_exists($file) ) {
        return realpath($file);
    }

    $paths = explode(PATH_SEPARATOR, get_include_path());

    foreach ( $paths as $path ) {
        $fullpath = $path . DIRECTORY_SEPARATOR . $file;

        if ( file_exists($fullpath) ) {
            return realpath($fullpath);
        }
    }

    return false;
}

function autoload($class) {
    $file = str_replace("\\", "/", $class) . '.php';
    if ( file_exists_in_include_path($file) ) {
        require_once $file;
    } else {
        // TODO: Throw exception!
    }
}

spl_autoload_register('autoload');

?>
