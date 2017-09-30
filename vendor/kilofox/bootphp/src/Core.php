<?php

namespace Bootphp;

use Bootphp\Exception\BootphpException;

/**
 * Contains the most low-level helpers methods in Bootphp:
 *
 * - Environment initialization
 * - Locating files within the cascading filesystem
 * - Auto-loading and transparent extension of classes
 * - Variable and path debugging
 *
 * @author      Tinsh <kilofox2000@gmail.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @license     http://kilofox.net/bootphp/license
 */
class Core
{
    // Release version
    const VERSION = '2.0.0';
    // Common environment type constants for consistency and convenience
    const PRODUCTION = 10;
    const STAGING = 20;
    const TESTING = 30;
    const DEVELOPMENT = 40;
    // Security check that is added to all generated PHP files
    const FILE_SECURITY = '<?php defined(\'SYS_PATH\') or exit(\'No direct script access.\');';
    // Format of cache files: header, cache name, and data
    const FILE_CACHE = ":header \n\n// :name\n\n:data\n";

    /**
     * @var  string  Current environment name
     */
    public static $environment = self::DEVELOPMENT;

    /**
     * @var  boolean  True if Bootphp is running on windows
     */
    public static $is_windows = false;

    /**
     * @var  boolean  True if [magic quotes](http://php.net/manual/en/security.magicquotes.php) is enabled.
     */
    public static $magic_quotes = false;

    /**
     * @var  boolean  true if PHP safe mode is on
     */
    public static $safe_mode = false;

    /**
     * @var  string
     */
    public static $content_type = 'text/html';

    /**
     * @var  string  character set of input and output
     */
    public static $charset = 'utf-8';

    /**
     * @var  string  the name of the server Bootphp is hosted upon
     */
    public static $server_name = '';

    /**
     * @var  array   list of valid host names for this instance
     */
    public static $hostnames = [];

    /**
     * @var  string  base URL to the application
     */
    public static $base_url = '/';

    /**
     * @var  string  Application index file, added to links generated by Bootphp. Set by [Core::init]
     */
    public static $index_file = 'index.php';

    /**
     * @var  string  Cache directory, used by [Core::cache]. Set by [Core::init]
     */
    public static $cache_dir;

    /**
     * @var  integer  Default lifetime for caching, in seconds, used by [Core::cache]. Set by [Core::init]
     */
    public static $cache_life = 60;

    /**
     * @var  boolean  Whether to use internal caching for [Core::find_file], does not apply to [Core::cache]. Set by [Core::init]
     */
    public static $caching = false;

    /**
     * @var  boolean  Whether to enable [profiling](bootphp/profiling). Set by [Core::init]
     */
    public static $profiling = true;

    /**
     * @var  boolean  Enable Bootphp catching and displaying PHP errors and exceptions. Set by [Core::init]
     */
    public static $errors = true;

    /**
     * @var  array  Types of errors to display at shutdown
     */
    public static $shutdown_errors = array(E_PARSE, E_ERROR, E_USER_ERROR);

    /**
     * @var  boolean  set the X-Powered-By header
     */
    public static $expose = false;

    /**
     * @var  Log  logging object
     */
    public static $log;

    /**
     * @var  Config  config object
     */
    public static $config;

    /**
     * @var  boolean  Has [Core::init] been called?
     */
    protected static $_init = false;

    /**
     * @var  array   Currently active modules
     */
    protected static $_modules = [];

    /**
     * @var  array   Include paths that are used to find files
     */
    protected static $_paths = array(APP_PATH, SYS_PATH);

    /**
     * @var  array   File path cache, used when caching is true in [Core::init]
     */
    protected static $_files = [];

    /**
     * @var  boolean  Has the file path cache changed during this execution?  Used internally when when caching is true in [Core::init]
     */
    protected static $_files_changed = false;

    /**
     * Initializes the environment:
     *
     * - Disables register_globals and magic_quotes_gpc
     * - Determines the current environment
     * - Set global settings
     * - Sanitizes GET, POST, and COOKIE variables
     * - Converts GET, POST, and COOKIE variables to the global character set
     *
     * The following settings can be set:
     *
     * Type      | Setting    | Description                                    | Default Value
     * ----------|------------|------------------------------------------------|---------------
     * `string`  | base_url   | The base URL for your application.  This should be the *relative* path from your ROOT_PATH to your `index.php` file, in other words, if Bootphp is in a subfolder, set this to the subfolder name, otherwise leave it as the default.  **The leading slash is required**, trailing slash is optional.   | `"/"`
     * `string`  | index_file | The name of the [front controller](http://en.wikipedia.org/wiki/Front_Controller_pattern).  This is used by Bootphp to generate relative urls like [HTML::anchor()] and [URL::base()]. This is usually `index.php`.  To [remove index.php from your urls](tutorials/clean-urls), set this to `false`. | `"index.php"`
     * `string`  | charset    | Character set used for all input and output    | `"utf-8"`
     * `string`  | cache_dir  | Bootphp's cache directory.  Used by [Core::cache] for simple internal caching, like [Fragments](bootphp/fragments) and **\[caching database queries](this should link somewhere)**.  This has nothing to do with the [Cache module](cache). | `APP_PATH."cache"`
     * `integer` | cache_life | Lifetime, in seconds, of items cached by [Core::cache]         | `60`
     * `boolean` | errors     | Should Bootphp catch PHP errors and uncaught Exceptions and show the `error_view`. See [Error Handling](bootphp/errors) for more info. <br /> <br /> Recommended setting: `true` while developing, `false` on production servers. | `true`
     * `boolean` | profile    | Whether to enable the [Profiler](bootphp/profiling). <br /> <br />Recommended setting: `true` while developing, `false` on production servers. | `true`
     * `boolean` | caching    | Cache file locations to speed up [Core::find_file].  This has nothing to do with [Core::cache], [Fragments](bootphp/fragments) or the [Cache module](cache).  <br /> <br />  Recommended setting: `false` while developing, `true` on production servers. | `false`
     * `boolean` | expose     | Set the X-Powered-By header
     *
     * @throws  BootphpException
     * @param   array   $settings   Array of settings.  See above.
     * @return  void
     * @uses    Core::globals
     * @uses    Core::sanitize
     * @uses    Core::cache
     * @uses    Profiler
     */
    public static function init(array $settings = null)
    {
        if (self::$_init) {
            // Do not allow execution twice
            return;
        }

        // Bootphp is now initialized
        self::$_init = true;

        if (isset($settings['profile'])) {
            // Enable profiling
            self::$profiling = (bool) $settings['profile'];
        }

        // Start an output buffer
        ob_start();

        if (isset($settings['errors'])) {
            // Enable error handling
            self::$errors = (bool) $settings['errors'];
        }

        if (self::$errors === true) {
            // Enable Bootphp exception handling, adds stack traces and error source.
            set_exception_handler(['Bootphp\Exception\BootphpException', 'handler']);

            // Enable Bootphp error handling, converts all PHP errors to exceptions.
            set_error_handler(['Bootphp\Core', 'error_handler']);
        }

        /**
         * Enable xdebug parameter collection in development mode to improve fatal stack traces.
         */
        if (self::$environment == self::DEVELOPMENT and extension_loaded('xdebug')) {
            ini_set('xdebug.collect_params', 3);
        }

        // Enable the Bootphp shutdown handler, which catches E_FATAL errors.
        register_shutdown_function(array('Bootphp\Core', 'shutdown_handler'));

        if (ini_get('register_globals')) {
            // Reverse the effects of register_globals
            self::globals();
        }

        if (isset($settings['expose'])) {
            self::$expose = (bool) $settings['expose'];
        }

        // Determine if we are running in a Windows environment
        self::$is_windows = (DIRECTORY_SEPARATOR === '\\');

        // Determine if we are running in safe mode
        self::$safe_mode = (bool) ini_get('safe_mode');

        if (isset($settings['cache_dir'])) {
            if (!is_dir($settings['cache_dir'])) {
                try {
                    // Create the cache directory
                    mkdir($settings['cache_dir'], 0755, true);

                    // Set permissions (must be manually set to fix umask issues)
                    chmod($settings['cache_dir'], 0755);
                } catch (\Exception $e) {
                    throw new BootphpException('Could not create cache directory :dir', array(':dir' => Debug::path($settings['cache_dir'])));
                }
            }

            // Set the cache directory path
            self::$cache_dir = realpath($settings['cache_dir']);
        } else {
            // Use the default cache directory
            self::$cache_dir = realpath(__DIR__ . '/../../../../data/cache');
        }

        if (!is_writable(self::$cache_dir)) {
            throw new BootphpException('Directory :dir must be writable', array(':dir' => Debug::path(self::$cache_dir)));
        }

        if (isset($settings['cache_life'])) {
            // Set the default cache lifetime
            self::$cache_life = (int) $settings['cache_life'];
        }

        if (isset($settings['caching'])) {
            // Enable or disable internal caching
            self::$caching = (bool) $settings['caching'];
        }

        if (self::$caching === true) {
            // Load the file path cache
            self::$_files = self::cache('Core::find_file()');
        }

        if (isset($settings['charset'])) {
            // Set the system character set
            self::$charset = strtolower($settings['charset']);
        }

        if (function_exists('mb_internal_encoding')) {
            // Set the MB extension encoding to the same character set
            mb_internal_encoding(self::$charset);
        }

        if (isset($settings['base_url'])) {
            // Set the base URL
            self::$base_url = rtrim($settings['base_url'], '/') . '/';
        }

        if (isset($settings['index_file'])) {
            // Set the index file
            self::$index_file = trim($settings['index_file'], '/');
        }

        // Determine if the extremely evil magic quotes are enabled
        self::$magic_quotes = (bool) get_magic_quotes_gpc();

        // Sanitize all request variables
        $_GET = self::sanitize($_GET);
        $_POST = self::sanitize($_POST);
        $_COOKIE = self::sanitize($_COOKIE);

        // Load the logger if one doesn't already exist
        if (!self::$log instanceof Log) {
            self::$log = Log::instance();
        }

        // Load the config if one doesn't already exist
        if (!self::$config instanceof Config) {
            self::$config = new Config;
        }
    }

    /**
     * Cleans up the environment:
     *
     * - Restore the previous error and exception handlers
     * - Destroy the Core::$log and Core::$config objects
     *
     * @return  void
     */
    public static function deinit()
    {
        if (self::$_init) {
            // Removed the autoloader
            spl_autoload_unregister(array('Bootphp', 'auto_load'));

            if (self::$errors) {
                // Go back to the previous error handler
                restore_error_handler();

                // Go back to the previous exception handler
                restore_exception_handler();
            }

            // Destroy objects created by init
            self::$log = self::$config = null;

            // Reset internal storage
            self::$_modules = self::$_files = [];
            self::$_paths = [APP_PATH, SYS_PATH];

            // Reset file cache status
            self::$_files_changed = false;

            // Bootphp is no longer initialized
            self::$_init = false;
        }
    }

    /**
     * Reverts the effects of the `register_globals` PHP setting by unsetting
     * all global variables except for the default super globals (GPCS, etc),
     * which is a [potential security hole.][ref-wikibooks]
     *
     * This is called automatically by [Core::init] if `register_globals` is
     * on.
     *
     *
     * [ref-wikibooks]: http://en.wikibooks.org/wiki/PHP_Programming/Register_Globals
     *
     * @return  void
     */
    public static function globals()
    {
        if (isset($_REQUEST['GLOBALS']) or isset($_FILES['GLOBALS'])) {
            // Prevent malicious GLOBALS overload attack
            echo "Global variable overload attack detected! Request aborted.\n";

            // Exit with an error status
            exit(1);
        }

        // Get the variable names of all globals
        $global_variables = array_keys($GLOBALS);

        // Remove the standard global variables from the list
        $global_variables = array_diff($global_variables, array(
            '_COOKIE',
            '_ENV',
            '_GET',
            '_FILES',
            '_POST',
            '_REQUEST',
            '_SERVER',
            '_SESSION',
            'GLOBALS',
        ));

        foreach ($global_variables as $name) {
            // Unset the global variable, effectively disabling register_globals
            unset($GLOBALS[$name]);
        }
    }

    /**
     * Recursively sanitizes an input variable:
     *
     * - Strips slashes if magic quotes are enabled
     * - Normalizes all newlines to LF
     *
     * @param   mixed   $value  any variable
     * @return  mixed   sanitized variable
     */
    public static function sanitize($value)
    {
        if (is_array($value) or is_object($value)) {
            foreach ($value as $key => $val) {
                // Recursively clean each value
                $value[$key] = self::sanitize($val);
            }
        } elseif (is_string($value)) {
            if (self::$magic_quotes === true) {
                // Remove slashes added by magic quotes
                $value = stripslashes($value);
            }

            if (strpos($value, "\r") !== false) {
                // Standardize newlines
                $value = str_replace(array("\r\n", "\r"), "\n", $value);
            }
        }

        return $value;
    }

    /**
     * Provides auto-loading support of classes that follow Bootphp's [class
     * naming conventions](bootphp/conventions#class-names-and-file-location).
     * See [Loading Classes](bootphp/autoloading) for more information.
     *
     *     // Loads classes/My/Class/Name.php
     *     Core::auto_load('My_Class_Name');
     *
     * or with a custom directory:
     *
     *     // Loads vendor/My/Class/Name.php
     *     Core::auto_load('My_Class_Name', 'vendor');
     *
     * You should never have to call this function, as simply calling a class
     * will cause it to be called.
     *
     * This function must be enabled as an autoloader in the bootstrap:
     *
     *     spl_autoload_register(array('Bootphp', 'auto_load'));
     *
     * @param   string  $class      Class name
     * @param   string  $directory  Directory to load from
     * @return  boolean
     */
    public static function auto_load($class, $directory = 'classes')
    {
        // Transform the class name according to PSR-0
        $class = ltrim($class, '\\');
        $file = '';
        $namespace = '';

        if ($last_namespace_position = strripos($class, '\\')) {
            $namespace = substr($class, 0, $last_namespace_position);
            $class = substr($class, $last_namespace_position + 1);
            $file = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }

        $file .= str_replace('_', DIRECTORY_SEPARATOR, $class);

        if ($path = self::find_file($directory, $file)) {
            // Load the class file
            require $path;

            // Class has been found
            return true;
        }

        // Class is not in the filesystem
        return false;
    }

    /**
     * Provides auto-loading support of classes that follow Bootphp's old class
     * naming conventions.
     *
     * This is included for compatibility purposes with older modules.
     *
     * @param   string  $class      Class name
     * @param   string  $directory  Directory to load from
     * @return  boolean
     */
    public static function auto_load_lowercase($class, $directory = 'classes')
    {
        // Transform the class name into a path
        $file = str_replace('_', DIRECTORY_SEPARATOR, strtolower($class));

        if ($path = self::find_file($directory, $file)) {
            // Load the class file
            require $path;

            // Class has been found
            return true;
        }

        // Class is not in the filesystem
        return false;
    }

    /**
     * Returns the the currently active include paths, including the
     * application, system, and each module's path.
     *
     * @return  array
     */
    public static function include_paths()
    {
        return self::$_paths;
    }

    /**
     * Searches for a file in the [Cascading Filesystem](bootphp/files), and
     * returns the path to the file that has the highest precedence, so that it
     * can be included.
     *
     * When searching the "config", "messages", or "i18n" directories, or when
     * the `$array` flag is set to true, an array of all the files that match
     * that path in the [Cascading Filesystem](bootphp/files) will be returned.
     * These files will return arrays which must be merged together.
     *
     * If no extension is given, the default extension `.php` will be used.
     *
     *     // Returns an absolute path to views/template.php
     *     Core::find_file('views', 'template');
     *
     *     // Returns an absolute path to media/css/style.css
     *     Core::find_file('media', 'css/style', 'css');
     *
     *     // Returns an array of all the "mimes" configuration files
     *     Core::find_file('config', 'mimes');
     *
     * @param   string  $dir    directory name (views, i18n, classes, extensions, etc.)
     * @param   string  $file   filename with subdirectory
     * @param   string  $ext    extension to search for
     * @param   boolean $array  return an array of files?
     * @return  array   a list of files when $array is true
     * @return  string  single file path
     */
    public static function find_file($dir, $file, $ext = null, $array = false)
    {
        if ($ext === null) {
            // Use the default extension
            $ext = '.php';
        } elseif ($ext) {
            // Prefix the extension with a period
            $ext = ".{$ext}";
        } else {
            // Use no extension
            $ext = '';
        }

        // Create a partial path of the filename
        $path = $dir . DIRECTORY_SEPARATOR . $file . $ext;

        if (self::$caching === true and isset(self::$_files[$path . ($array ? '_array' : '_path')])) {
            // This path has been cached
            return self::$_files[$path . ($array ? '_array' : '_path')];
        }

        if (self::$profiling === true and class_exists('Profiler', false)) {
            // Start a new benchmark
            $benchmark = Profiler::start('Bootphp', __FUNCTION__);
        }

        if ($array or $dir === 'config' or $dir === 'i18n' or $dir === 'messages') {
            // Include paths must be searched in reverse
            $paths = array_reverse(self::$_paths);

            // Array of files that have been found
            $found = [];

            foreach ($paths as $dir) {
                if (is_file($dir . $path)) {
                    // This path has a file, add it to the list
                    $found[] = $dir . $path;
                }
            }
        } else {
            // The file has not been found yet
            $found = false;

            foreach (self::$_paths as $dir) {
                $file = $dir . DIRECTORY_SEPARATOR . $path;
                if (is_file($file)) {
                    // A path has been found
                    $found = $file;

                    // Stop searching
                    break;
                }
            }
        }

        if (self::$caching === true) {
            // Add the path to the cache
            self::$_files[$path . ($array ? '_array' : '_path')] = $found;

            // Files have been changed
            self::$_files_changed = true;
        }

        if (isset($benchmark)) {
            // Stop the benchmark
            Profiler::stop($benchmark);
        }

        return $found;
    }

    /**
     * Recursively finds all of the files in the specified directory at any
     * location in the [Cascading Filesystem](bootphp/files), and returns an
     * array of all the files found, sorted alphabetically.
     *
     *     // Find all view files.
     *     $views = Core::list_files('views');
     *
     * @param   string  $directory  directory name
     * @param   array   $paths      list of paths to search
     * @return  array
     */
    public static function list_files($directory = null, array $paths = null)
    {
        if ($directory !== null) {
            // Add the directory separator
            $directory .= DIRECTORY_SEPARATOR;
        }

        if ($paths === null) {
            // Use the default paths
            $paths = self::$_paths;
        }

        // Create an array for the files
        $found = [];

        foreach ($paths as $path) {
            if (is_dir($path . $directory)) {
                // Create a new directory iterator
                $dir = new DirectoryIterator($path . $directory);

                foreach ($dir as $file) {
                    // Get the file name
                    $filename = $file->getFilename();

                    if ($filename[0] === '.' or $filename[strlen($filename) - 1] === '~') {
                        // Skip all hidden files and UNIX backup files
                        continue;
                    }

                    // Relative filename is the array key
                    $key = $directory . $filename;

                    if ($file->isDir()) {
                        if ($sub_dir = self::list_files($key, $paths)) {
                            if (isset($found[$key])) {
                                // Append the sub-directory list
                                $found[$key] += $sub_dir;
                            } else {
                                // Create a new sub-directory list
                                $found[$key] = $sub_dir;
                            }
                        }
                    } else {
                        if (!isset($found[$key])) {
                            // Add new files to the list
                            $found[$key] = realpath($file->getPathName());
                        }
                    }
                }
            }
        }

        // Sort the results alphabetically
        ksort($found);

        return $found;
    }

    /**
     * Loads a file within a totally empty scope and returns the output:
     *
     *     $foo = Core::load('foo.php');
     *
     * @param   string  $file
     * @return  mixed
     */
    public static function load($file)
    {
        return include $file;
    }

    /**
     * Provides simple file-based caching for strings and arrays:
     *
     *     // Set the "foo" cache
     *     Core::cache('foo', 'hello, world');
     *
     *     // Get the "foo" cache
     *     $foo = Core::cache('foo');
     *
     * All caches are stored as PHP code, generated with [var_export][ref-var].
     * Caching objects may not work as expected. Storing references or an
     * object or array that has recursion will cause an E_FATAL.
     *
     * The cache directory and default cache lifetime is set by [Core::init]
     *
     * [ref-var]: http://php.net/var_export
     *
     * @throws  BootphpException
     * @param   string  $name       name of the cache
     * @param   mixed   $data       data to cache
     * @param   integer $lifetime   number of seconds the cache is valid for
     * @return  mixed    for getting
     * @return  boolean  for setting
     */
    public static function cache($name, $data = null, $lifetime = null)
    {
        // Cache file is a hash of the name
        $file = sha1($name) . '.txt';

        // Cache directories are split by keys to prevent filesystem overload
        $dir = self::$cache_dir . DIRECTORY_SEPARATOR . $file[0] . $file[1] . DIRECTORY_SEPARATOR;

        if ($lifetime === null) {
            // Use the default lifetime
            $lifetime = self::$cache_life;
        }

        if ($data === null) {
            if (is_file($dir . $file)) {
                if ((time() - filemtime($dir . $file)) < $lifetime) {
                    // Return the cache
                    try {
                        return unserialize(file_get_contents($dir . $file));
                    } catch (\Exception $e) {
                        // Cache is corrupt, let return happen normally.
                    }
                } else {
                    try {
                        // Cache has expired
                        unlink($dir . $file);
                    } catch (\Exception $e) {
                        // Cache has mostly likely already been deleted,
                        // let return happen normally.
                    }
                }
            }

            // Cache not found
            return null;
        }

        if (!is_dir($dir)) {
            // Create the cache directory
            mkdir($dir, 0777, true);

            // Set permissions (must be manually set to fix umask issues)
            chmod($dir, 0777);
        }

        // Force the data to be a string
        $data = serialize($data);

        try {
            // Write the cache
            return (bool) file_put_contents($dir . $file, $data, LOCK_EX);
        } catch (\Exception $e) {
            // Failed to write cache
            return false;
        }
    }

    /**
     * Get a message from a file. Messages are arbitrary strings that are stored
     * in the `messages/` directory and reference by a key. Translation is not
     * performed on the returned values.  See [message files](bootphp/files/messages)
     * for more information.
     *
     *     // Get "username" from messages/text.php
     *     $username = Core::message('text', 'username');
     *
     * @param   string  $file       file name
     * @param   string  $path       key path to get
     * @param   mixed   $default    default value if the path does not exist
     * @return  string  message string for the given path
     * @return  array   complete message list, when no path is specified
     * @uses    Arr::merge
     * @uses    Arr::path
     */
    public static function message($file, $path = null, $default = null)
    {
        static $messages;

        if (!isset($messages[$file])) {
            // Create a new message list
            $messages[$file] = [];

            if ($files = self::find_file('messages', $file)) {
                foreach ($files as $f) {
                    // Combine all the messages recursively
                    $messages[$file] = Arr::merge($messages[$file], self::load($f));
                }
            }
        }

        if ($path === null) {
            // Return all of the messages
            return $messages[$file];
        } else {
            // Get a message using the path
            return Arr::path($messages[$file], $path, $default);
        }
    }

    /**
     * PHP error handler, converts all errors into ErrorExceptions. This handler
     * respects error_reporting settings.
     *
     * @throws  ErrorException
     * @return  true
     */
    public static function error_handler($code, $error, $file = null, $line = null)
    {
        if (error_reporting() & $code) {
            // This error is not suppressed by current error reporting settings
            // Convert the error into an ErrorException
            throw new \ErrorException($error, $code, 0, $file, $line);
        }

        // Do not execute the PHP error handler
        return true;
    }

    /**
     * Catches errors that are not caught by the error handler, such as E_PARSE.
     *
     * @uses    BootphpException::handler
     * @return  void
     */
    public static function shutdown_handler()
    {
        if (!self::$_init) {
            // Do not execute when not active
            return;
        }

        try {
            if (self::$caching === true and self::$_files_changed === true) {
                // Write the file path cache
                self::cache('Core::find_file()', self::$_files);
            }
        } catch (\Exception $e) {
            // Pass the exception to the handler
            BootphpException::handler($e);
        }

        if (self::$errors and $error = error_get_last() and in_array($error['type'], self::$shutdown_errors)) {
            // Clean the output buffer
            ob_get_level() and ob_clean();

            // Fake an exception for nice debugging
            BootphpException::handler(new \ErrorException($error['message'], $error['type'], 0, $error['file'], $error['line']));

            // Shutdown now to avoid a "death loop"
            exit(1);
        }
    }

    /**
     * Generates a version string based on the variables defined above.
     *
     * @return string
     */
    public static function version()
    {
        return 'Bootphp Framework ' . self::VERSION;
    }

}
