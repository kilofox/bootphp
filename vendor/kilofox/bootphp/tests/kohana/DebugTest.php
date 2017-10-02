<?php

/**
 * Tests Bootphp Core
 *
 * @TODO Use a virtual filesystem (see phpunit doc on mocking fs) for find_file etc.
 *
 * @group bootphp
 * @group bootphp.core
 * @group bootphp.core.debug
 *
 * @author      Tinsh <kilofox2000@gmail.com>
 * @author     Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @license     http://kilofox.net/bootphp/license
 */
class Bootphp_DebugTest extends Unittest_TestCase
{
    /**
     * Provides test data for test_debug()
     *
     * @return array
     */
    public function provider_vars()
    {
        return array(
            // $thing, $expected
            array(array('foobar'), "<pre class=\"debug\"><small>array</small><span>(1)</span> <span>(\n    0 => <small>string</small><span>(6)</span> \"foobar\"\n)</span></pre>"),
        );
    }

    /**
     * Tests Debug::vars()
     *
     * @test
     * @dataProvider provider_vars
     * @covers Debug::vars
     * @param boolean $thing    The thing to debug
     * @param boolean $expected Output for Debug::vars
     */
    public function test_var($thing, $expected)
    {
        $this->assertEquals($expected, Debug::vars($thing));
    }

    /**
     * Provides test data for testDebugPath()
     *
     * @return array
     */
    public function provider_debug_path()
    {
        return array(
            array(
                SYS_PATH . 'classes' . DIRECTORY_SEPARATOR . 'bootphp.php',
                'SYS_PATH' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'bootphp.php'
            ),
            array(
                MOD_PATH . $this->dirSeparator('unittest/classes/bootphp/unittest/runner') . '.php',
                $this->dirSeparator('MOD_PATH/unittest/classes/bootphp/unittest/runner') . '.php'
            ),
        );
    }

    /**
     * Tests Debug::path()
     *
     * @test
     * @dataProvider provider_debug_path
     * @covers Debug::path
     * @param boolean $path     Input for Debug::path
     * @param boolean $expected Output for Debug::path
     */
    public function test_debug_path($path, $expected)
    {
        $this->assertEquals($expected, Debug::path($path));
    }

    /**
     * Provides test data for test_dump()
     *
     * @return array
     */
    public function provider_dump()
    {
        return array(
            array('foobar', 128, 10, '<small>string</small><span>(6)</span> "foobar"'),
            array('foobar', 2, 10, '<small>string</small><span>(6)</span> "fo&nbsp;&hellip;"'),
            array(null, 128, 10, '<small>null</small>'),
            array(true, 128, 10, '<small>bool</small> true'),
            array(array('foobar'), 128, 10, "<small>array</small><span>(1)</span> <span>(\n    0 => <small>string</small><span>(6)</span> \"foobar\"\n)</span>"),
            array(new StdClass, 128, 10, "<small>object</small> <span>stdClass(0)</span> <code>{\n}</code>"),
            array("fo\x6F\xFF\x00bar\x8F\xC2\xB110", 128, 10, '<small>string</small><span>(10)</span> "foobar±10"'),
            array(array('level1' => array('level2' => array('level3' => array('level4' => array('value' => 'something'))))), 128, 4,
                '<small>array</small><span>(1)</span> <span>(
    "level1" => <small>array</small><span>(1)</span> <span>(
        "level2" => <small>array</small><span>(1)</span> <span>(
            "level3" => <small>array</small><span>(1)</span> <span>(
                "level4" => <small>array</small><span>(1)</span> (
                    ...
                )
            )</span>
        )</span>
    )</span>
)</span>'),
        );
    }

    /**
     * Tests Debug::dump()
     *
     * @test
     * @dataProvider provider_dump
     * @covers Debug::dump
     * @covers Debug::_dump
     * @param object $exception exception to test
     * @param string $expected  expected output
     */
    public function test_dump($input, $length, $limit, $expected)
    {
        $this->assertEquals($expected, Debug::dump($input, $length, $limit));
    }

}
