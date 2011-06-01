<?php

// TODO: Move to a libraries class!
define('CONTROLLER_LOCATION', 'app\controller\\');

// TODO: Move to a libraries class!
define('MODEL_LOCATION', 'app\model\\');

// TODO: Move all of these to a templates class!
define('DEFAULT_CONTROLLER', 'Dashboard');
define('DEFAULT_ACTION', 'index');
define('DEFAULT_TEMPLATE', 'default');

require __DIR__ . '/bootstrap/autoloader.php';

require __DIR__ . '/bootstrap/cache.php';

require __DIR__ . '/bootstrap/db.php';

?>
