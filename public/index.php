<?php

    ob_start();

    require "../config/config.php";

    // TODO: Fix Sessions
    // core\Session::start();

    $controller = new Controller();
    $controller->render($_SERVER['QUERY_STRING']);

    ob_end_flush();

?>
