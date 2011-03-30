<?php

define('DATABASE_USER', 'root');
define('DATABASE_PASS', 'admin');
define('DATABASE_HOST', 'localhost');
define('DATABASE_NAME', 'test');

define('MEMCACHED_HOST', '127.0.0.1');

define('CSRF_TOKEN_SALT', 'JU7]h{I^Wic)');









// The directory where PHPUnit is installed
define('PHPUNIT_INSTALL', '/usr/share/pear/PHPUnit');

set_include_path(get_include_path().PATH_SEPARATOR.
                 PHPUNIT_INSTALL);

// The directory where historical snapshots will be stored
define('SNAPSHOT_DIRECTORY', 'history');

// The directory where the tests reside
define('VPU_TEST_DIRECTORY', '../test');

// VPU scans the test directory supplied above and will only include files 
// containing VPU_TEST_FILENAME (case-insensitive) within their filenames
define('VPU_TEST_FILENAME', 'Test');

/*
* Optional settings
*/

// Whether or not to create snapshots of the test results
define('VPU_CREATE_SNAPSHOTS', false);

// Whether or not to sandbox PHP errors
define('VPU_SANDBOX_ERRORS', true);

// The file to use as a temporary storage for PHP errors during PHPUnit runs
define('VPU_SANDBOX_FILENAME', BASE_INSTALL . '/test/errors/errors.tmp');

?>
