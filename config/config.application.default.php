<?

define("DATABASE_USER", "root");
define("DATABASE_PASS", "Admin");
define("DATABASE_HOST", "db.dev");
define("DATABASE_NAME", "buildingadvice");

define("MEMCACHED_HOST", "127.0.0.1");


// The directory where PHPUnit is installed
define('PHPUNIT_INSTALL', '/usr/share/pear/PHPUnit');

set_include_path(get_include_path().PATH_SEPARATOR.
                 PHPUNIT_INSTALL.PATH_SEPARATOR);

// The directory where the tests reside
define('VPU_TEST_DIRECTORY', 'tests');

// VPU scans the test directory supplied above and will only include files 
// containing VPU_TEST_FILENAME (case-insensitive) within their filenames
define('VPU_TEST_FILENAME', 'Test');

/*
* Optional settings
*/

// Whether or not to create snapshots of the test results
define('VPU_CREATE_SNAPSHOTS', false);

// The directory where the test results will be stored
define('VPU_SNAPSHOT_DIRECTORY', 'history');

// Whether or not to sandbox PHP errors
define('VPU_SANDBOX_ERRORS', false);

// The file to use as a temporary storage for PHP errors during PHPUnit runs
define('VPU_SANDBOX_FILENAME', BASE_INSTALL . '/errors/errors.tmp');

?>
