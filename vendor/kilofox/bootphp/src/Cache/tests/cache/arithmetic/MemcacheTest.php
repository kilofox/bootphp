<?php

namespace Bootphp\Cache\tests\cache\arithmetic;

use Bootphp\Filesystem;

include_once(Filesystem::findFile('tests/cache/arithmetic', 'CacheArithmeticMethods'));
/**
 * @group      bootphp
 * @group      bootphp.cache
 * @author      Tinsh <kilofox2000@gmail.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @license     http://kilofox.net/bootphp/license
 */
class Bootphp_CacheArithmeticMemcacheTest extends Bootphp_CacheArithmeticMethodsTest
{
    /**
     * This method MUST be implemented by each driver to setup the `Cache`
     * instance for each test.
     *
     * This method should do the following tasks for each driver test:
     *
     *  - Test the Cache instance driver is available, skip test otherwise
     *  - Setup the Cache instance
     *  - Call the parent setup method, `parent::setUp()`
     *
     * @return  void
     */
    public function setUp()
    {
        parent::setUp();

        if (!extension_loaded('memcache')) {
            $this->markTestSkipped('Memcache PHP Extension is not available');
        }
        if (!$config = Core::$config->load('cache.memcache')) {
            Core::$config->load('cache')
                ->set(
                    'memcache', array(
                    'driver' => 'memcache',
                    'default_expire' => 3600,
                    'compression' => false, // Use Zlib compression (can cause issues with integers)
                    'servers' => array(
                        'local' => array(
                            'host' => 'localhost', // Memcache Server
                            'port' => 11211, // Memcache port number
                            'persistent' => false, // Persistent connection
                            'weight' => 1,
                            'timeout' => 1,
                            'retry_interval' => 15,
                            'status' => true,
                        ),
                    ),
                    'instant_death' => true,
                    )
            );
            $config = Core::$config->load('cache.memcache');
        }

        $memcache = new Memcache;
        if (!$memcache->connect($config['servers']['local']['host'], $config['servers']['local']['port'])) {
            $this->markTestSkipped('Unable to connect to memcache server @ ' .
                $config['servers']['local']['host'] . ':' .
                $config['servers']['local']['port']);
        }

        if ($memcache->getVersion() === false) {
            $this->markTestSkipped('Memcache server @ ' .
                $config['servers']['local']['host'] . ':' .
                $config['servers']['local']['port'] .
                ' not responding!');
        }

        unset($memcache);

        $this->cache(Cache::instance('memcache'));
    }

    /**
     * Tests that multiple values set with Memcache do not cause unexpected
     * results. For accurate results, this should be run with a memcache
     * configuration that includes multiple servers.
     *
     * This is to test #4110
     *
     * @link    http://dev.kilofox.net/issues/4110
     * @return  void
     */
    public function test_multiple_set()
    {
        $cache = $this->cache();
        $id_set = 'set_id';
        $ttl = 300;

        $data = array(
            'foobar',
            0,
            1.0,
            new stdClass,
            array('foo', 'bar' => 1),
            true,
            null,
            false
        );

        $previous_set = $cache->get($id_set, null);

        foreach ($data as $value) {
            // Use Equals over Sames as Objects will not be equal
            $this->assertEquals($previous_set, $cache->get($id_set, null));
            $cache->set($id_set, $value, $ttl);

            $previous_set = $value;
        }
    }

}
