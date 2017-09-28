<?php

if (isset($_ENV['TRAVIS'])) {
    // This is really hacky, but without it the result is permanently full of noise that makes it impossible to see
    // any unexpected skipped tests.
    print "Skipping all Wincache driver tests as these will never run on Travis." . \PHP_EOL;
    return;
} else {
    include_once(Core::find_file('tests/cache', 'CacheBasicMethodsTest'));
    /**
     * @group      bootphp
     * @group      bootphp.cache
     * @author      Tinsh <kilofox2000@gmail.com>
     * @copyright   (C) 2013-2017 Kilofox Studio
     * @license     http://kilofox.net/bootphp/license
     */
    class Bootphp_WincacheTest extends Bootphp_CacheBasicMethodsTest
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

            if (!extension_loaded('wincache')) {
                $this->markTestSkipped('Wincache PHP Extension is not available');
            }

            $this->cache(Cache::instance('wincache'));
        }

    }

}
