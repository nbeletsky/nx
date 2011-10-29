<?php

$nx_root = dirname(dirname(dirname(__DIR__)));

set_include_path(
    get_include_path()  . PATH_SEPARATOR .
    $nx_root            . PATH_SEPARATOR
);

/**
*  Checks to see if a file exists within the include path.
*
*  @param string $file              The file.
*  @return bool
*/
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

/**
*  Serves as the global class autoloader.
*
*  @param string $class             The class to load.
*  @return bool
*/
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
