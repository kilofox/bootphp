<?php

/**
 * @group      bootphp
 * @group      bootphp.image
 * @author      Tinsh <kilofox2000@gmail.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @license    http://http://kilofox.net/license
 */
class Bootphp_ImageTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!extension_loaded('gd')) {
            $this->markTestSkipped('The GD extension is not available.');
        }
    }

    /**
     * Tests the Image::save() method for files that don't have extensions
     *
     * @return  void
     */
    public function test_save_without_extension()
    {
        $image = Image::factory(MOD_PATH . 'image/tests/test_data/test_image');
        $this->assertTrue($image->save(Core::$cache_dir . '/test_image'));

        unlink(Core::$cache_dir . '/test_image');
    }

}
