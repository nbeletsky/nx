<?php

    require dirname(__DIR__) . '/config/bootstrap.php';

    $args = \nx\lib\Dispatcher::parse_query_string($_SERVER['QUERY_STRING']);
    $args['post'] = ( !empty($_POST) ) ? \nx\lib\Data::extract_post($_POST) : array();

    \nx\lib\Dispatcher::render($args);

?>
