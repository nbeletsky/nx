<?php

    ob_start();

    require "../config/config.php";

    $args = \nx\lib\Dispatcher::parse_query_string($_SERVER['QUERY_STRING']);
    $args['post'] = ( !empty($_POST) ) ? Data::extract_post($_POST) : array();

    \nx\lib\Dispatcher::render($args);

    ob_end_flush();

?>
