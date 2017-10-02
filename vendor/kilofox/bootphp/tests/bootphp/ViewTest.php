<?php

/**
 * Tests the View class
 *
 * @group bootphp
 * @group bootphp.core
 * @group bootphp.core.view
 *
 * @author      Tinsh <kilofox2000@gmail.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @license     http://kilofox.net/bootphp/license
 */
class Bootphp_ViewTest extends Unittest_TestCase
{
    protected static $old_modules = [];

    /**
     * Setups the filesystem for test view files
     *
     * @return null
     */
    // @codingStandardsIgnoreStart
    public static function setupBeforeClass()
    // @codingStandardsIgnoreEnd
    {
        self::$old_modules = Core::modules();

        $new_modules = self::$old_modules + array(
            'test_views' => realpath(dirname(__FILE__) . '/../test_data/')
        );
        Core::modules($new_modules);
    }

    /**
     * Restores the module list
     *
     * @return null
     */
    // @codingStandardsIgnoreStart
    public static function teardownAfterClass()
    // @codingStandardsIgnoreEnd
    {
        Core::modules(self::$old_modules);
    }

    /**
     * Provider for test_instaniate
     *
     * @return array
     */
    public function provider_instantiate()
    {
        return array(
            array('bootphp/error', false),
            array('test.css', false),
            array('doesnt_exist', true),
        );
    }

    /**
     * Provider to test_set
     *
     * @return array
     */
    public function provider_set()
    {
        return array(
            array('foo', 'bar', 'foo', 'bar'),
            array(array('foo' => 'bar'), null, 'foo', 'bar'),
            array(new ArrayIterator(array('foo' => 'bar')), null, 'foo', 'bar'),
        );
    }

    /**
     * Tests that we can instantiate a view file
     *
     * @test
     * @dataProvider provider_instantiate
     *
     * @return null
     */
    public function test_instantiate($path, $expects_exception)
    {
        try {
            $view = new View($path);
            $this->assertSame(false, $expects_exception);
        } catch (\Bootphp\BootphpException $e) {
            $this->assertSame(true, $expects_exception);
        }
    }

    /**
     * Tests that we can set using string, array or Traversable object
     *
     * @test
     * @dataProvider provider_set
     *
     * @return null
     */
    public function test_set($data_key, $value, $test_key, $expected)
    {
        $view = View::factory()->set($data_key, $value);
        $this->assertSame($expected, $view->$test_key);
    }

    /**
     * Tests that we can set global using string, array or Traversable object
     *
     * @test
     * @dataProvider provider_set
     *
     * @return null
     */
    public function test_set_global($data_key, $value, $test_key, $expected)
    {
        $view = View::factory();
        $view::set_global($data_key, $value);
        $this->assertSame($expected, $view->$test_key);
    }

}
