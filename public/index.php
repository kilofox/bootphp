<?php

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
 * Set the default time zone.
 *
 * @link http://kilofox.net/guide/using.configuration
 * @link http://www.php.net/manual/timezones
 */
date_default_timezone_set('UTC');

/**
 * Set the default locale.
 *
 * @link http://kilofox.net/guide/using.configuration
 * @link http://www.php.net/manual/function.setlocale
 */
setlocale(LC_ALL, 'en_US.utf-8');

// Define the absolute paths.
define('PUB_PATH', __DIR__);
define('APP_PATH', realpath(__DIR__ . '/..'));
define('VEN_PATH', realpath(__DIR__ . '/../vendor'));

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

// Enable the Bootphp auto-loader.
require __DIR__ . '/../vendor/autoload.php';

/**
 * Set the default language
 */
Bootphp\I18n::lang('en-us');

if (isset($_SERVER['SERVER_PROTOCOL'])) {
    // Replace the default protocol.
    Bootphp\Http\Http::$protocol = $_SERVER['SERVER_PROTOCOL'];
}

/**
 * Set Core::$environment if a 'BOOTPHP_ENV' environment variable has been supplied.
 *
 * Note: If you supply an invalid environment name, a PHP warning will be thrown
 * saying "Couldn't find constant Core::<INVALID_ENV_NAME>"
 */
if (isset($_SERVER['BOOTPHP_ENV'])) {
    Bootphp\Core::$environment = constant('Core::' . strtoupper($_SERVER['BOOTPHP_ENV']));
}

/**
 * Initialize Bootphp, setting the default options.
 *
 * The following options are available:
 *
 * - string   base_url    path, and optionally domain, of your application   null
 * - string   index_file  name of your index file, usually "index.php"       index.php
 * - string   charset     internal character set used for input and output   utf-8
 * - string   cache_dir   set the internal cache directory                   APP_PATH/cache
 * - integer  cache_life  lifetime, in seconds, of items cached              60
 * - boolean  errors      enable or disable error handling                   true
 * - boolean  profile     enable or disable internal profiling               true
 * - boolean  caching     enable or disable internal caching                 false
 * - boolean  expose      set the X-Powered-By header                        false
 */
Bootphp\Core::init(array(
    'base_url' => '/frameworks/bootphp/public',
));

/**
 * Attach the file write to logging. Multiple writers are supported.
 */
Bootphp\Core::$log->attach(new \Bootphp\Log\File(realpath(__DIR__ . '/../data/log')));

/**
 * Attach a file reader to config. Multiple readers are supported.
 */
Bootphp\Core::$config->attach(new \Bootphp\Config\File);

/**
 * Cookie Salt
 * @see  http://kilofox.net/3.3/guide/bootphp/cookies
 *
 * If you have not defined a cookie salt in your Cookie class then
 * uncomment the line below and define a preferrably long salt.
 */
Bootphp\Cookie::$salt = 'null';

/**
 * Set the routes. Each route must have a minimum of a name, a URI and a set of
 * defaults for the URI.
 */

// Static file serving (CSS, JS, images)
Bootphp\Route::set('doc/media', 'doc/media(/<file>)', array('file' => '.+'))
    ->defaults(array(
        'directory' => 'doc',
        'controller' => 'index',
        'action' => 'media',
        'file' => null,
    ));

// API Browser, if enabled
if (Bootphp\Core::$config->load('userguide.api_browser') === true) {
    Bootphp\Route::set('doc/api', 'guide-api(/<class>)', array('class' => '[a-zA-Z0-9_]+'))
        ->defaults(array(
            'controller' => 'Userguide',
            'action' => 'api',
            'class' => null,
    ));
}

Bootphp\Route::set('doc', '(<directory>(/<controller>(/<id>)(/<action>)))')
    ->defaults(array(
        'directory' => 'doc',
        'controller' => 'index',
        'action' => 'index',
    ));

// User guide pages, in modules
Bootphp\Route::set('doc/guide', 'guide(/<module>(/<page>))', array(
        'page' => '.+',
    ))
    ->defaults(array(
        'controller' => 'Userguide',
        'action' => 'docs',
        'module' => '',
    ));

Bootphp\Route::set('default', '(<controller>(/<action>(/<id>)))')
    ->defaults(array(
        'controller' => 'welcome',
        'action' => 'index',
    ));

if (PHP_SAPI == 'cli') {
    // Try and load minion
    class_exists('Minion_Task') or exit('Please enable the Minion module for CLI support.');
    set_exception_handler(array('Minion_Exception', 'handler'));

    Minion_Task::factory(Minion_CLI::options())->execute();
} else {
    /**
     * Execute the main request. A source of the URI can be passed, eg: $_SERVER['PATH_INFO'].
     * If no source is specified, the URI will be automatically detected.
     */
    echo \Bootphp\Request\Request::factory(true, [], false)
        ->execute()
        ->send_headers(true)
        ->body();
}
