<?php

\nx\lib\Connections::add_cache('default', array(
    'plugin'        => 'Memcached',
    'host'          => 'localhost',
    'persistent_id' => ''
));

?>
