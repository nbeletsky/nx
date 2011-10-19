<?php

$dev = array(
    'plugin'        => 'Memcached',
    'host'          => 'localhost',
    'persistent_id' => ''
);

/*
$qa = array(
    'plugin'        => 'Memcached',
    'host'          => 'localhost',
    'persistent_id' => ''
);

$ci = array(
    'plugin'        => 'Memcached',
    'host'          => 'localhost',
    'persistent_id' => ''
);

$live = array(
    'plugin'        => 'Memcached',
    'host'          => 'localhost',
    'persistent_id' => ''
);
*/

\nx\lib\Connections::add_cache('default', $dev);

?>
