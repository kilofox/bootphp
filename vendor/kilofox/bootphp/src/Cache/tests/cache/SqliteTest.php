<?php

include_once(Core::find_file('tests/cache', 'CacheBasicMethodsTest'));
/**
 * @group      bootphp
 * @group      bootphp.cache
 * @author      Tinsh <kilofox2000@gmail.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @license     http://kilofox.net/bootphp/license
 */
class Bootphp_SqliteTest extends Bootphp_CacheBasicMethodsTest
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

        if (!extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('SQLite PDO PHP Extension is not available');
        }

        if (!Core::$config->load('cache.sqlite')) {
            Core::$config->load('cache')
                ->set(
                    'sqlite', array(
                    'driver' => 'sqlite',
                    'default_expire' => 3600,
                    'database' => 'memory',
                    'schema' => 'CREATE TABLE caches(id VARCHAR(127) PRIMARY KEY, tags VARCHAR(255), expiration INTEGER, cache TEXT)',
                    )
            );
        }

        $this->cache(Cache::instance('sqlite'));
    }

}
