<?php

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

/**
 * Set the default language
 */
Bootphp\I18n::lang('en-us');

if (isset($_SERVER['SERVER_PROTOCOL'])) {
    // Replace the default protocol.
    Bootphp\Http::$protocol = $_SERVER['SERVER_PROTOCOL'];
}

/**
 * Set Core::$environment if a 'BOOTPHP_ENV' environment variable has been supplied.
 *
 * Note: If you supply an invalid environment name, a PHP warning will be thrown
 * saying "Couldn't find constant Core::<INVALID_ENV_NAME>"
 */
if (isset($_SERVER['BOOTPHP_ENV'])) {
    Core::$environment = constant('Core::' . strtoupper($_SERVER['BOOTPHP_ENV']));
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
    'base_url' => '/bootcms/',
));

/**
 * Attach the file write to logging. Multiple writers are supported.
 */
Bootphp\Core::$log->attach(new \Bootphp\Log\File(APP_PATH . DIRECTORY_SEPARATOR . 'logs'));

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
//Bootphp\Route::set('admin', '(<directory>(/<controller>(/<id>)(/<action>)))', [
//    'directory' => '(admin)',
//    'id' => '\d+'
//    ]
//);
Bootphp\Route::set('default', '(<controller>(/<action>(/<id>)))')
    ->defaults(array(
        'controller' => 'welcome',
        'action' => 'index',
    ));
