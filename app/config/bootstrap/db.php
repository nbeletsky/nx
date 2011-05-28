<?php

\nx\lib\Connections::add_db('default', array(
    'plugin'   => 'PDO_MySQL',
    'database' => 'journal',
    'host'     => 'localhost',
    'user'     => 'root',
    'password' => 'admin'
));

?>
