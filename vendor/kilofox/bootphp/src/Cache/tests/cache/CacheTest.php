<?php

/**
 * @group      bootphp
 * @group      bootphp.cache
 * @author      Tinsh <kilofox2000@gmail.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @license     http://kilofox.net/bootphp/license
 */
class Bootphp_CacheTest extends PHPUnit_Framework_TestCase
{
    const BAD_GROUP_DEFINITION = 1010;
    const EXPECT_SELF = 1001;

    /**
     * Data provider for test_instance
     *
     * @return  array
     */
    public function provider_instance()
    {
        $tmp = realpath(sys_get_temp_dir());

        $base = [];

        if (Core::$config->load('cache.file')) {
            $base = array(
                // Test default group
                array(
                    null,
                    Cache::instance('file')
                ),
                // Test defined group
                array(
                    'file',
                    Cache::instance('file')
                ),
            );
        }


        return array(
            // Test bad group definition
            $base + array(
            Bootphp_CacheTest::BAD_GROUP_DEFINITION,
            'Failed to load Bootphp Cache group: 1010'
            ),
        );
    }

    /**
     * Tests the [Cache::factory()] method behaves as expected
     *
     * @dataProvider provider_instance
     *
     * @return  void
     */
    public function test_instance($group, $expected)
    {
        if (in_array($group, array(
                Bootphp_CacheTest::BAD_GROUP_DEFINITION,
                )
            )) {
            $this->setExpectedException('Cache_Exception');
        }

        try {
            $cache = Cache::instance($group);
        } catch (Cache_Exception $e) {
            $this->assertSame($expected, $e->getMessage());
            throw $e;
        }

        $this->assertInstanceOf(get_class($expected), $cache);
        $this->assertSame($expected->config(), $cache->config());
    }

    /**
     * Tests that `clone($cache)` will be prevented to maintain singleton
     *
     * @return  void
     * @expectedException Cache_Exception
     */
    public function test_cloning_fails()
    {
        $cache = $this->getMockBuilder('Cache')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        try {
            clone($cache);
        } catch (Cache_Exception $e) {
            $this->assertSame('Cloning of Bootphp_Cache objects is forbidden', $e->getMessage());
            throw $e;
        }
    }

    /**
     * Data provider for test_config
     *
     * @return  array
     */
    public function provider_config()
    {
        return array(
            array(
                array(
                    'server' => 'otherhost',
                    'port' => 5555,
                    'persistent' => true,
                ),
                null,
                Bootphp_CacheTest::EXPECT_SELF,
                array(
                    'server' => 'otherhost',
                    'port' => 5555,
                    'persistent' => true,
                ),
            ),
            array(
                'foo',
                'bar',
                Bootphp_CacheTest::EXPECT_SELF,
                array(
                    'foo' => 'bar'
                )
            ),
            array(
                'server',
                null,
                null,
                []
            ),
            array(
                null,
                null,
                [],
                []
            )
        );
    }

    /**
     * Tests the config method behaviour
     *
     * @dataProvider provider_config
     *
     * @param   mixed    key value to set or get
     * @param   mixed    value to set to key
     * @param   mixed    expected result from [Cache::config()]
     * @param   array    expected config within cache
     * @return  void
     */
    public function test_config($key, $value, $expected_result, array $expected_config)
    {
        $cache = $this->getMock('Cache_File', null, [], '', false);

        if ($expected_result === Bootphp_CacheTest::EXPECT_SELF) {
            $expected_result = $cache;
        }

        $this->assertSame($expected_result, $cache->config($key, $value));
        $this->assertSame($expected_config, $cache->config());
    }

    /**
     * Data provider for test_sanitize_id
     *
     * @return  array
     */
    public function provider_sanitize_id()
    {
        return array(
            array(
                'foo',
                'foo'
            ),
            array(
                'foo+-!@',
                'foo+-!@'
            ),
            array(
                'foo/bar',
                'foo_bar',
            ),
            array(
                'foo\\bar',
                'foo_bar'
            ),
            array(
                'foo bar',
                'foo_bar'
            ),
            array(
                'foo\\bar snafu/stfu',
                'foo_bar_snafu_stfu'
            )
        );
    }

    /**
     * Tests the [Cache::_sanitize_id()] method works as expected.
     * This uses some nasty reflection techniques to access a protected
     * method.
     *
     * @dataProvider provider_sanitize_id
     *
     * @param   string    id
     * @param   string    expected
     * @return  void
     */
    public function test_sanitize_id($id, $expected)
    {
        $cache = $this->getMock('Cache', array(
            'get',
            'set',
            'delete',
            'delete_all'
            ), array([]), '', false
        );

        $cache_reflection = new ReflectionClass($cache);
        $sanitize_id = $cache_reflection->getMethod('_sanitize_id');
        $sanitize_id->setAccessible(true);

        $this->assertSame($expected, $sanitize_id->invoke($cache, $id));
    }

}
