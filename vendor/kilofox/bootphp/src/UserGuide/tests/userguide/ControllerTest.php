<?php

/**
 * Unit tests for internal methods of userguide controller
 *
 * @group bootphp
 * @group bootphp.userguide
 * @group bootphp.userguide.controller
 *
 * @author      Tinsh <kilofox2000@gmail.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @license     http://kilofox.net/bootphp/license
 */
class Userguide_ControllerTest extends Unittest_TestCase
{
    public function provider_file_finds_markdown_files()
    {
        return array(
            array('userguide/adding', 'guide/userguide/adding.md'),
            array('userguide/adding.md', 'guide/userguide/adding.md'),
            array('userguide/adding.markdown', 'guide/userguide/adding.md'),
            array('userguide/does_not_exist.md', false)
        );
    }

    /**
     * @dataProvider provider_file_finds_markdown_files
     * @param  string  $page           Page name passed in the URL
     * @param  string  $expected_file  Expected result from Controller_Userguide::file
     */
    public function test_file_finds_markdown_files($page, $expected_file)
    {
        $controller = $this->getMock('Controller_Userguide', array('__construct'), [], '', false);
        $path = $controller->file($page);

        // Only verify trailing segments to avoid problems if file overwritten in CFS
        $expected_len = strlen($expected_file);
        $file = substr($path, -$expected_len, $expected_len);

        $this->assertEquals($expected_file, $file);
    }

}
