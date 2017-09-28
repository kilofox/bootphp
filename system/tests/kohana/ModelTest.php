<?php

/**
 * This test only really exists for code coverage.
 *
 * @group bootphp
 * @group bootphp.core
 * @group bootphp.core.model
 *
 * @author      Tinsh <kilofox2000@gmail.com>
 * @author     BRMatt <matthew@sigswitch.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @license     http://kilofox.net/bootphp/license
 */
class Bootphp_ModelTest extends Unittest_TestCase
{
    /**
     * Test the model's factory.
     *
     * @test
     * @covers Model::factory
     */
    public function test_create()
    {
        $foobar = Model::factory('Foobar');

        $this->assertEquals(true, $foobar instanceof Model);
    }

}

class Model_Foobar extends Model
{

}
