<?php

    ob_start();

    require "../config/config.php";

    $page = new lib\Page();
    $page->render($_SERVER['QUERY_STRING']);

    ob_end_flush();

?>
