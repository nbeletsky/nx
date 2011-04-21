<?php

    ob_start();

    require "../config/config.php";

    \nx\lib\Page::render($_SERVER['QUERY_STRING']);

    ob_end_flush();

?>
