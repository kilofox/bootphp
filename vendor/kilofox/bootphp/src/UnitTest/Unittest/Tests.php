<?php

/**
 * PHPUnit testsuite for bootphp application
 *
 * @author      Tinsh <kilofox2000@gmail.com>
 * @author     BRMatt <matthew@sigswitch.com>
 * @author      Tinsh <kilofox2000@gmail.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @license     http://kilofox.net/bootphp/license
 */
class Bootphp_Unittest_Tests
{
    static protected $cache = [];

    /**
     * Loads test files if they cannot be found by bootphp
     * @param <type> $class
     */
    static function autoload($class)
    {
        $file = str_replace('_', '/', $class);

        if ($file = Core::find_file('tests', $file)) {
            require_once $file;
        }
    }

    /**
     * Configures the environment for testing
     *
     * Does the following:
     *
     * * Loads the phpunit framework (for the web ui)
     * * Restores exception phpunit error handlers (for cli)
     * * registeres an autoloader to load test files
     */
    static public function configure_environment($do_whitelist = true, $do_blacklist = true)
    {
        restore_exception_handler();
        restore_error_handler();

        spl_autoload_register(array('Unittest_tests', 'autoload'));

        Unittest_tests::$cache = (($cache = Core::cache('unittest_whitelist_cache')) === null) ? [] : $cache;
    }

    /**
     * Creates the test suite for bootphp
     *
     * @return Unittest_TestSuite
     */
    static function suite()
    {
        static $suite = null;

        if ($suite instanceof PHPUnit_Framework_TestSuite) {
            return $suite;
        }

        Unittest_Tests::configure_environment();

        $suite = new Unittest_TestSuite;

        // Load the whitelist and blacklist for code coverage
        $config = Core::$config->load('unittest');

        if ($config->use_whitelist) {
            Unittest_Tests::whitelist(null, $suite);
        }

        if (count($config['blacklist'])) {
            Unittest_Tests::blacklist($config->blacklist, $suite);
        }

        // Add tests
        $files = Core::list_files('tests');
        self::addTests($suite, $files);

        return $suite;
    }

    /**
     * Add files to test suite $suite
     *
     * Uses recursion to scan subdirectories
     *
     * @param Unittest_TestSuite  $suite   The test suite to add to
     * @param array                        $files   Array of files to test
     */
    static function addTests(Unittest_TestSuite $suite, array $files)
    {

        foreach ($files as $path => $file) {
            if (is_array($file)) {
                if ($path != 'tests' . DIRECTORY_SEPARATOR . 'test_data') {
                    self::addTests($suite, $file);
                }
            } else {
                // Make sure we only include php files
                if (is_file($file) and substr($file, -4) === '.php') {
                    // The default PHPUnit TestCase extension
                    if (!strpos($file, 'TestCase.php')) {
                        $suite->addTestFile($file);
                    } else {
                        require_once($file);
                    }

                    $suite->addFileToBlacklist($file);
                }
            }
        }
    }

    /**
     * Blacklist a set of files in PHPUnit code coverage
     *
     * @param array $blacklist_items A set of files to blacklist
     * @param Unittest_TestSuite $suite The test suite
     */
    static public function blacklist(array $blacklist_items, Unittest_TestSuite $suite = null)
    {
        foreach ($blacklist_items as $item) {
            if (is_dir($item)) {
                $suite->addDirectoryToBlacklist($item);
            } else {
                $suite->addFileToBlacklist($item);
            }
        }
    }

    /**
     * Sets the whitelist
     *
     * If no directories are provided then the function'll load the whitelist
     * set in the config file
     *
     * @param array $directories Optional directories to whitelist
     * @param Unittest_Testsuite $suite Suite to load the whitelist into
     */
    static public function whitelist(array $directories = null, Unittest_TestSuite $suite = null)
    {
        if (empty($directories)) {
            $directories = self::get_config_whitelist();
        }

        if (count($directories)) {
            foreach ($directories as & $directory) {
                $directory = realpath($directory) . '/';
            }

            // Only whitelist the "top" files in the cascading filesystem
            self::set_whitelist(Core::list_files('classes', $directories), $suite);
        }
    }

    /**
     * Works out the whitelist from the config
     * Used only on the CLI
     *
     * @returns array Array of directories to whitelist
     */
    static protected function get_config_whitelist()
    {
        $config = Core::$config->load('unittest');
        $directories = [];

        if ($config->whitelist['app']) {
            $directories['k_app'] = APP_PATH;
        }

        if ($modules = $config->whitelist['modules']) {
            $k_modules = Core::modules();

            // Have to do this because bootphp merges config...
            // If you want to include all modules & override defaults then true must be the first
            // value in the modules array of your app/config/unittest file
            if (array_search(true, $modules, true) === (count($modules) - 1)) {
                $modules = $k_modules;
            } elseif (array_search(false, $modules, true) === false) {
                $modules = array_intersect_key($k_modules, array_combine($modules, $modules));
            } else {
                // modules are disabled
                $modules = [];
            }

            $directories += $modules;
        }

        if ($config->whitelist['system']) {
            $directories['k_sys'] = SYS_PATH;
        }

        return $directories;
    }

    /**
     * Recursively whitelists an array of files
     *
     * @param array $files Array of files to whitelist
     * @param Unittest_TestSuite $suite Suite to load the whitelist into
     */
    static protected function set_whitelist($files, Unittest_TestSuite $suite = null)
    {

        foreach ($files as $file) {
            if (is_array($file)) {
                self::set_whitelist($file, $suite);
            } else {
                if (!isset(Unittest_tests::$cache[$file])) {
                    $relative_path = substr($file, strrpos($file, 'classes' . DIRECTORY_SEPARATOR) + 8, -4);
                    $cascading_file = Core::find_file('classes', $relative_path);

                    // The theory is that if this file is the highest one in the cascading filesystem
                    // then it's safe to whitelist
                    Unittest_tests::$cache[$file] = ($cascading_file === $file);
                }

                if (Unittest_tests::$cache[$file]) {
                    if (isset($suite)) {
                        $suite->addFileToWhitelist($file);
                    } else {
                        PHPUnit_Util_Filter::addFileToWhitelist($file);
                    }
                }
            }
        }
    }

}
