<?php

$dev = array(
    'plugin'   => 'PDO_MySQL',
    'database' => '',
    'host'     => 'localhost',
    'username' => 'root',
    'password' => 'admin'
);

/*
$qa = array(
    'plugin'   => 'PDO_MySQL',
    'database' => '',
    'host'     => 'localhost',
    'username' => 'root',
    'password' => 'admin'
);

$ci = array(
    'plugin'   => 'PDO_MySQL',
    'database' => '',
    'host'     => 'localhost',
    'username' => 'root',
    'password' => 'admin'
);

$live = array(
    'plugin'   => 'PDO_MySQL',
    'database' => '',
    'host'     => 'localhost',
    'username' => 'root',
    'password' => 'admin'
);
*/

\nx\lib\Connections::add_db('default', $dev);

?>
