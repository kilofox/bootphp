<?php

/**
 * Tests Bootphp upload class
 *
 * @group bootphp
 * @group bootphp.core
 * @group bootphp.core.upload
 *
 * @author      Tinsh <kilofox2000@gmail.com>
 * @author     Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @license     http://kilofox.net/bootphp/license
 */
class Bootphp_UploadTest extends Unittest_TestCase
{
    /**
     * Provides test data for test_size()
     *
     * @return array
     */
    public function provider_size()
    {
        return array(
            // $field, $bytes, $environment, $expected
            array(
                'unit_test',
                5,
                array('_FILES' => array('unit_test' => array('error' => UPLOAD_ERR_INI_SIZE))),
                false
            ),
            array(
                'unit_test',
                5,
                array('_FILES' => array('unit_test' => array('error' => UPLOAD_ERR_NO_FILE))),
                true
            ),
            array(
                'unit_test',
                '6K',
                array('_FILES' => array(
                        'unit_test' => array(
                            'error' => UPLOAD_ERR_OK,
                            'name' => 'Unit_Test File',
                            'type' => 'image/png',
                            'tmp_name' => Core::find_file('tests', 'test_data/github', 'png'),
                            'size' => filesize(Core::find_file('tests', 'test_data/github', 'png')),
                        )
                    )
                ),
                true
            ),
            array(
                'unit_test',
                '1B',
                array('_FILES' => array(
                        'unit_test' => array(
                            'error' => UPLOAD_ERR_OK,
                            'name' => 'Unit_Test File',
                            'type' => 'image/png',
                            'tmp_name' => Core::find_file('tests', 'test_data/github', 'png'),
                            'size' => filesize(Core::find_file('tests', 'test_data/github', 'png')),
                        )
                    )
                ),
                false
            ),
        );
    }

    /**
     * Tests Upload::size
     *
     * @test
     * @dataProvider provider_size
     * @covers upload::size
     * @param string $field the files field to test
     * @param string $bytes valid bite size
     * @param array $environment set the $_FILES array
     * @param bool $expected what to expect
     */
    public function test_size($field, $bytes, $environment, $expected)
    {
        $this->setEnvironment($environment);

        $this->assertSame($expected, Upload::size($_FILES[$field], $bytes));
    }

    /**
     * size() should throw an exception of the supplied max size is invalid
     *
     * @test
     * @covers upload::size
     * @expectedException BootphpException
     */
    public function test_size_throws_exception_for_invalid_size()
    {
        $this->setEnvironment(array(
            '_FILES' => array(
                'unit_test' => array(
                    'error' => UPLOAD_ERR_OK,
                    'name' => 'Unit_Test File',
                    'type' => 'image/png',
                    'tmp_name' => Core::find_file('tests', 'test_data/github', 'png'),
                    'size' => filesize(Core::find_file('tests', 'test_data/github', 'png')),
                )
            )
        ));

        Upload::size($_FILES['unit_test'], '1DooDah');
    }

    /**
     * Provides test data for test_vali()
     *
     * @test
     * @return array
     */
    public function provider_valid()
    {
        return array(
            array(
                true,
                array(
                    'error' => UPLOAD_ERR_OK,
                    'name' => 'Unit_Test File',
                    'type' => 'image/png',
                    'tmp_name' => Core::find_file('tests', 'test_data/github', 'png'),
                    'size' => filesize(Core::find_file('tests', 'test_data/github', 'png')),
                )
            ),
            array(
                false,
                array(
                    'name' => 'Unit_Test File',
                    'type' => 'image/png',
                    'tmp_name' => Core::find_file('tests', 'test_data/github', 'png'),
                    'size' => filesize(Core::find_file('tests', 'test_data/github', 'png')),
                )
            ),
            array(
                false,
                array(
                    'error' => UPLOAD_ERR_OK,
                    'type' => 'image/png',
                    'tmp_name' => Core::find_file('tests', 'test_data/github', 'png'),
                    'size' => filesize(Core::find_file('tests', 'test_data/github', 'png')),
                )
            ),
            array(
                false,
                array(
                    'name' => 'Unit_Test File',
                    'error' => UPLOAD_ERR_OK,
                    'tmp_name' => Core::find_file('tests', 'test_data/github', 'png'),
                    'size' => filesize(Core::find_file('tests', 'test_data/github', 'png')),
                )
            ),
            array(
                false,
                array(
                    'error' => UPLOAD_ERR_OK,
                    'name' => 'Unit_Test File',
                    'type' => 'image/png',
                    'size' => filesize(Core::find_file('tests', 'test_data/github', 'png')),
                )
            ),
            array(
                false,
                array(
                    'error' => UPLOAD_ERR_OK,
                    'name' => 'Unit_Test File',
                    'type' => 'image/png',
                    'tmp_name' => Core::find_file('tests', 'test_data/github', 'png'),
                )
            ),
        );
    }

    /**
     * Test Upload::valid
     *
     * @test
     * @dataProvider provider_valid
     * @covers Upload::valid
     */
    public function test_valid($expected, $file)
    {
        $this->setEnvironment(array(
            '_FILES' => array(
                'unit_test' => $file,
            ),
        ));

        $this->assertSame($expected, Upload::valid($_FILES['unit_test']));
    }

    /**
     * Tests Upload::type
     *
     * @test
     * @covers Upload::type
     */
    public function test_type()
    {
        $this->setEnvironment(array(
            '_FILES' => array(
                'unit_test' => array(
                    'error' => UPLOAD_ERR_OK,
                    'name' => 'github.png',
                    'type' => 'image/png',
                    'tmp_name' => Core::find_file('tests', 'test_data/github', 'png'),
                    'size' => filesize(Core::find_file('tests', 'test_data/github', 'png')),
                )
            )
        ));

        $this->assertTrue(Upload::type($_FILES['unit_test'], array('jpg', 'png', 'gif')));

        $this->assertFalse(Upload::type($_FILES['unit_test'], array('docx')));
    }

}
