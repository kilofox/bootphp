<?php

namespace Bootphp\tests\bootphp;

use Bootphp\Filesystem;

/**
 * Tests Bootphp Core
 *
 * @TODO Use a virtual filesystem (see phpunit doc on mocking fs) for findFile etc.
 *
 * @group bootphp
 * @group bootphp.core
 * @group bootphp.core.core
 *
 * @author      Tinsh <kilofox2000@gmail.com>
 * @author     Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @license     http://kilofox.net/bootphp/license
 */
class Bootphp_CoreTest extends Unittest_TestCase
{
    protected $old_modules = [];

    /**
     * Captures the module list as it was before this test
     *
     * @return null
     */
    // @codingStandardsIgnoreStart
    public function setUp()
    // @codingStandardsIgnoreEnd
    {
        parent::setUp();
        $this->old_modules = Core::modules();
    }

    /**
     * Restores the module list
     *
     * @return null
     */
    // @codingStandardsIgnoreStart
    public function tearDown()
    // @codingStandardsIgnoreEnd
    {
        Core::modules($this->old_modules);
    }

    /**
     * Provides test data for test_sanitize()
     *
     * @return array
     */
    public function provider_sanitize()
    {
        return array(
            // $value, $result
            array('foo', 'foo'),
            array("foo\r\nbar", "foo\nbar"),
            array("foo\rbar", "foo\nbar"),
            array("Is your name O\'reilly?", "Is your name O'reilly?")
        );
    }

    /**
     * Tests Core::santize()
     *
     * @test
     * @dataProvider provider_sanitize
     * @covers Core::sanitize
     * @param boolean $value  Input for Core::sanitize
     * @param boolean $result Output for Core::sanitize
     */
    public function test_sanitize($value, $result)
    {
        $this->setEnvironment(array('Core::$magic_quotes' => true));

        $this->assertSame($result, Core::sanitize($value));
    }

    /**
     * Passing false for the file extension should prevent appending any extension.
     * See issue #3214
     *
     * @test
     * @covers  Filesystem::findFile
     */
    public function test_findFile_no_extension()
    {
        $path = Filesystem::findFile('classes', $file = 'Bootphp/Core.php', false);

        $this->assertInternalType('string', $path);

        $this->assertStringEndsWith($file, $path);
    }

    /**
     * If a file can't be found then findFile() should return false if
     * only a single file was requested, or an empty array if multiple files
     * (i.e. configuration files) were requested
     *
     * @test
     * @covers Filesystem::findFile
     */
    public function test_findFile_returns_false_or_array_on_failure()
    {
        $this->assertFalse(Filesystem::findFile('configy', 'zebra'));

        $this->assertSame([], Filesystem::findFile('configy', 'zebra', null, true));
    }

    /**
     * Core::list_files() should return an array on success and an empty array on failure
     *
     * @test
     * @covers Core::list_files
     */
    public function test_list_files_returns_array_on_success_and_failure()
    {
        $files = Core::list_files('config');

        $this->assertInternalType('array', $files);
        $this->assertGreaterThan(3, count($files));

        $this->assertSame([], Core::list_files('geshmuck'));
    }

    /**
     * Tests Core::globals()
     *
     * @test
     * @covers Core::globals
     */
    public function test_globals_removes_user_def_globals()
    {
        $GLOBALS['hackers'] = 'foobar';
        $GLOBALS['name'] = array('', '', '');
        $GLOBALS['_POST'] = [];

        Core::globals();

        $this->assertFalse(isset($GLOBALS['hackers']));
        $this->assertFalse(isset($GLOBALS['name']));
        $this->assertTrue(isset($GLOBALS['_POST']));
    }

    /**
     * Provides test data for testCache()
     *
     * @return array
     */
    public function provider_cache()
    {
        return array(
            // $value, $result
            array('foo', 'hello, world', 10),
            array('bar', null, 10),
            array('bar', null, -10),
        );
    }

    /**
     * Tests Core::cache()
     *
     * @test
     * @dataProvider provider_cache
     * @covers Core::cache
     * @param boolean $key      Key to cache/get for Core::cache
     * @param boolean $value    Output from Core::cache
     * @param boolean $lifetime Lifetime for Core::cache
     */
    public function test_cache($key, $value, $lifetime)
    {
        Core::cache($key, $value, $lifetime);
        $this->assertEquals($value, Core::cache($key));
    }

    /**
     * Provides test data for test_message()
     *
     * @return array
     */
    public function provider_message()
    {
        return array(
            array('no_message_file', 'anything', 'default', 'default'),
            array('no_message_file', null, 'anything', []),
            array('bootphp_core_message_tests', 'bottom_only', 'anything', 'inherited bottom message'),
            array('bootphp_core_message_tests', 'cfs_replaced', 'anything', 'overriding cfs_replaced message'),
            array('bootphp_core_message_tests', 'top_only', 'anything', 'top only message'),
            array('bootphp_core_message_tests', 'missing', 'default', 'default'),
            array('bootphp_core_message_tests', null, 'anything',
                array(
                    'bottom_only' => 'inherited bottom message',
                    'cfs_replaced' => 'overriding cfs_replaced message',
                    'top_only' => 'top only message'
                )
            ),
        );
    }

    /**
     * Tests Core::message()
     *
     * @test
     * @dataProvider provider_message
     * @covers       Core::message
     * @param string $file     to pass to Core::message
     * @param string $key      to pass to Core::message
     * @param string $default  to pass to Core::message
     * @param string $expected Output for Core::message
     */
    public function test_message($file, $key, $default, $expected)
    {
        $test_path = realpath(dirname(__FILE__) . '/../test_data/message_tests');
        Core::modules(array('top' => "$test_path/top_module", 'bottom' => "$test_path/bottom_module"));

        $this->assertEquals($expected, Core::message($file, $key, $default, $expected));
    }

    /**
     * Provides test data for test_error_handler()
     *
     * @return array
     */
    public function provider_error_handler()
    {
        return array(
            array(1, 'Foobar', 'foobar.php', __LINE__),
        );
    }

    /**
     * Tests Core::error_handler()
     *
     * @test
     * @dataProvider provider_error_handler
     * @covers Core::error_handler
     * @param boolean $code  Input for Core::sanitize
     * @param boolean $error  Input for Core::sanitize
     * @param boolean $file  Input for Core::sanitize
     * @param boolean $line Output for Core::sanitize
     */
    public function test_error_handler($code, $error, $file, $line)
    {
        $error_level = error_reporting();
        error_reporting(E_ALL);
        try {
            Core::error_handler($code, $error, $file, $line);
        } catch (\Exception $e) {
            $this->assertEquals($code, $e->getCode());
            $this->assertEquals($error, $e->getMessage());
        }
        error_reporting($error_level);
    }

    /**
     * Provides test data for test_modules_sets_and_returns_valid_modules()
     *
     * @return array
     */
    public function provider_modules_detects_invalid_modules()
    {
        return array(
            array(array('unittest' => MOD_PATH . 'fo0bar')),
            array(array('unittest' => MOD_PATH . 'unittest', 'fo0bar' => MOD_PATH . 'fo0bar')),
        );
    }

    /**
     * Tests Core::modules()
     *
     * @test
     * @dataProvider provider_modules_detects_invalid_modules
     * @expectedException BootphpException
     * @param boolean $source   Input for Core::modules
     *
     */
    public function test_modules_detects_invalid_modules($source)
    {
        $modules = Core::modules();

        try {
            Core::modules($source);
        } catch (\Exception $e) {
            // Restore modules
            Core::modules($modules);

            throw $e;
        }

        // Restore modules
        Core::modules($modules);
    }

    /**
     * Provides test data for test_modules_sets_and_returns_valid_modules()
     *
     * @return array
     */
    public function provider_modules_sets_and_returns_valid_modules()
    {
        return array(
            array([], []),
            array(array('module' => __DIR__), array('module' => $this->dirSeparator(__DIR__ . '/'))),
        );
    }

    /**
     * Tests Core::modules()
     *
     * @test
     * @dataProvider provider_modules_sets_and_returns_valid_modules
     * @param boolean $source   Input for Core::modules
     * @param boolean $expected Output for Core::modules
     */
    public function test_modules_sets_and_returns_valid_modules($source, $expected)
    {
        $modules = Core::modules();

        try {
            $this->assertEquals($expected, Core::modules($source));
        } catch (\Exception $e) {
            Core::modules($modules);

            throw $e;
        }

        Core::modules($modules);
    }

    /**
     * To make the tests as portable as possible this just tests that
     * you get an array of modules when you can Core::modules() and that
     * said array contains unittest
     *
     * @test
     * @covers Core::modules
     */
    public function test_modules_returns_array_of_modules()
    {
        $modules = Core::modules();

        $this->assertInternalType('array', $modules);

        $this->assertArrayHasKey('unittest', $modules);
    }

    /**
     * Tests Core::include_paths()
     *
     * The include paths must contain the apppath and syspath
     * @test
     * @covers Core::include_paths
     */
    public function test_include_paths()
    {
        $include_paths = Core::include_paths();
        $modules = Core::modules();

        $this->assertInternalType('array', $include_paths);

        // We must have at least 2 items in include paths (APP / SYS)
        $this->assertGreaterThan(2, count($include_paths));
        // Make sure said paths are in the include paths
        // And make sure they're in the correct positions
        $this->assertSame(APP_PATH, reset($include_paths));
        $this->assertSame(SYS_PATH, end($include_paths));

        foreach ($modules as $module) {
            $this->assertContains($module, $include_paths);
        }
    }

}
