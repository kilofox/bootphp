<?php

include_once(Core::find_file('tests/cache/arithmetic', 'CacheArithmeticMethods'));
/**
 * @group      bootphp
 * @group      bootphp.cache
 * @author      Tinsh <kilofox2000@gmail.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @license     http://kilofox.net/bootphp/license
 */
class Bootphp_ApcuTest extends Bootphp_CacheArithmeticMethodsTest
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

        if (!extension_loaded('apcu')) {
            $this->markTestSkipped('APCu PHP Extension is not available');
        }

        if (!(ini_get('apc.enabled') and ini_get('apc.enable_cli'))) {
            $this->markTestSkipped('APCu is not enabled. To fix ' .
                'set "apc.enabled=1" and "apc.enable_cli=1" in your php.ini file');
        }

        if (!Core::$config->load('cache.apcu')) {
            Core::$config->load('cache')
                ->set(
                    'apcu', array(
                    'driver' => 'apcu',
                    'default_expire' => 3600,
                    )
            );
        }

        $this->cache(Cache::instance('apcu'));
    }

    /**
     * Tests the [Cache::set()] method, testing;
     *
     *  - The value is cached
     *  - The lifetime is respected
     *  - The returned value type is as expected
     *  - The default not-found value is respected
     *
     * This test doesn't test the TTL as there is a known bug/feature
     * in APCu that prevents the same request from killing cache on timeout.
     *
     * @link   http://pecl.php.net/bugs/bug.php?id=16814
     *
     * @dataProvider provider_set_get
     *
     * @param   array    data
     * @param   mixed    expected
     * @return  void
     */
    public function test_set_get(array $data, $expected)
    {
        if ($data['wait'] !== false) {
            $this->markTestSkipped('Unable to perform TTL test in CLI, see: ' .
                'http://pecl.php.net/bugs/bug.php?id=16814 for more info!');
        }

        parent::test_set_get($data, $expected);
    }

}