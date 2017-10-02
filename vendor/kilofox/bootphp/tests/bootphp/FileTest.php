<?php

/**
 * Tests Bootphp File helper
 *
 * @group bootphp
 * @group bootphp.core
 * @group bootphp.core.file
 *
 * @author      Tinsh <kilofox2000@gmail.com>
 * @author     Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @license     http://kilofox.net/bootphp/license
 */
class Bootphp_FileTest extends Unittest_TestCase
{
    /**
     * Provides test data for test_sanitize()
     *
     * @return array
     */
    public function provider_mime()
    {
        return array(
            // $value, $result
            array(Core::find_file('tests', 'test_data/github', 'png'), 'image/png'),
        );
    }

    /**
     * Tests File::mime()
     *
     * @test
     * @dataProvider provider_mime
     * @param boolean $input  Input for File::mime
     * @param boolean $expected Output for File::mime
     */
    public function test_mime($input, $expected)
    {
        //@todo: File::mime coverage needs significant improvement or to be dropped for a composer package - it's a "horribly unreliable" method with very little testing
        $this->assertSame($expected, File::mime($input));
    }

    /**
     * Provides test data for test_split_join()
     *
     * @return array
     */
    public function provider_split_join()
    {
        return array(
            // $value, $result
            array(Core::find_file('tests', 'test_data/github', 'png'), .01, 1),
        );
    }

    /**
     * Tests File::mime()
     *
     * @test
     * @dataProvider provider_split_join
     * @param boolean $input    Input for File::split
     * @param boolean $peices   Input for File::split
     * @param boolean $expected Output for File::splut
     */
    public function test_split_join($input, $peices, $expected)
    {
        $this->assertSame($expected, File::split($input, $peices));
        $this->assertSame($expected, File::join($input));

        foreach (glob(Core::find_file('tests', 'test_data/github', 'png') . '.*') as $file) {
            unlink($file);
        }
    }

}
