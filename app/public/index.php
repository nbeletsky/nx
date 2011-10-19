<?php
    require dirname(__DIR__) . '/config/bootstrap.php';

    echo \nx\lib\Dispatcher::render($_SERVER['REQUEST_URI']);
?>
