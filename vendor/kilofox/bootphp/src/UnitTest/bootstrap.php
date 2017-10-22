<?php

/**
 * The directory in which your application specific resources are located.
 * The application directory must contain the bootstrap.php file.
 *
 * @link http://kilofox.net/guide/about.install#application
 */
$application = 'application';

/**
 * The directory in which your modules are located.
 *
 * @link http://kilofox.net/guide/about.install#modules
 */
$modules = 'modules';

/**
 * The directory in which the Bootphp resources are located. The system
 * directory must contain the classes/bootphp.php file.
 *
 * @link http://kilofox.net/guide/about.install#system
 */
$system = 'system';

/**
 * Set the path to the document root
 *
 * This assumes that this file is stored 2 levels below the PUB_PATH, if you move
 * this bootstrap file somewhere else then you'll need to modify this value to
 * compensate.
 */
define('PUB_PATH', realpath(dirname(__FILE__) . '/../../') . DIRECTORY_SEPARATOR);

/**
 * Set the PHP error reporting level. If you set this in php.ini, you remove this.
 * @link http://www.php.net/manual/errorfunc.configuration#ini.error-reporting
 *
 * When developing your application, it is highly recommended to enable notices
 * and strict warnings. Enable them by using: E_ALL | E_STRICT
 *
 * In a production environment, it is safe to ignore notices and strict warnings.
 * Disable them by using: E_ALL ^ E_NOTICE
 *
 * When using a legacy application with PHP >= 5.3, it is recommended to disable
 * deprecated notices. Disable with: E_ALL & ~E_DEPRECATED
 */
error_reporting(E_ALL | E_STRICT);

/**
 * End of standard configuration! Changing any of the code below should only be
 * attempted by those with a working knowledge of Bootphp internals.
 *
 * @link http://kilofox.net/guide/using.configuration
 */
// Make the application relative to the docroot
if (!is_dir($application) and is_dir(PUB_PATH . $application)) {
    $application = PUB_PATH . $application;
}

// Make the modules relative to the docroot
if (!is_dir($modules) and is_dir(PUB_PATH . $modules)) {
    $modules = PUB_PATH . $modules;
}

// Make the system relative to the docroot
if (!is_dir($system) and is_dir(PUB_PATH . $system)) {
    $system = PUB_PATH . $system;
}

// Define the absolute paths for configured directories
define('APP_PATH', realpath($application) . DIRECTORY_SEPARATOR);
define('MOD_PATH', realpath($modules) . DIRECTORY_SEPARATOR);
define('SYS_PATH', realpath($system) . DIRECTORY_SEPARATOR);

// Clean up the configuration vars
unset($application, $modules, $system);

/**
 * Define the start time of the application, used for profiling.
 */
if (!defined('START_TIME')) {
    define('START_TIME', microtime(true));
}

/**
 * Define the memory usage at the start of the application, used for profiling.
 */
if (!defined('START_MEMORY')) {
    define('START_MEMORY', memory_get_usage());
}

// Bootstrap the application
require APP_PATH . 'bootstrap.php';

// Disable output buffering
if (($ob_len = ob_get_length()) !== false) {
    // flush_end on an empty buffer causes headers to be sent. Only flush if needed.
    if ($ob_len > 0) {
        ob_end_flush();
    } else {
        ob_end_clean();
    }
}

// Enable the unittest module if it is not already loaded - use the absolute path
$modules = Core::modules();
$unittest_path = realpath(__DIR__) . DIRECTORY_SEPARATOR;
if (!in_array($unittest_path, $modules)) {
    $modules['unittest'] = $unittest_path;
    Core::modules($modules);
}
