<?php

/**
 * Tests Bootphp Exception Class
 *
 * @group bootphp
 * @group bootphp.core
 * @group bootphp.core.exception
 *
 * @author      Tinsh <kilofox2000@gmail.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @license     http://kilofox.net/bootphp/license
 */
class BootphpExceptionTest extends Unittest_TestCase
{
    /**
     * Provides test data for test_constructor()
     *
     * @return array
     */
    public function provider_constructor()
    {
        return array(
            array(array(''), '', 0),
            array(array(':a'), ':a', 0),
            array(array(':a', null), ':a', 0),
            array(array(':a', []), ':a', 0),
            array(array(':a', array(':a' => 'b')), 'b', 0),
            array(array(':a :b', array(':a' => 'c', ':b' => 'd')), 'c d', 0),
            array(array(':a', null, 5), ':a', 5),
            // #3358
            array(array(':a', null, '3F000'), ':a', '3F000'),
            // #3404
            array(array(':a', null, '42S22'), ':a', '42S22'),
            // #3927
            array(array(':a', null, 'b'), ':a', 'b'),
            // #4039
            array(array(':a', null, '25P01'), ':a', '25P01'),
        );
    }

    /**
     * Tests Bootphp_BootphpException::__construct()
     *
     * @test
     * @dataProvider provider_constructor
     * @covers Bootphp_BootphpException::__construct
     * @param array             $arguments          Arguments
     * @param string            $expected_message   Value from getMessage()
     * @param integer|string    $expected_code      Value from getCode()
     */
    public function test_constructor($arguments, $expected_message, $expected_code)
    {
        switch (count($arguments)) {
            case 1:
                $exception = new BootphpException(reset($arguments));
                break;
            case 2:
                $exception = new BootphpException(reset($arguments), next($arguments));
                break;
            default:
                $exception = new BootphpException(reset($arguments), next($arguments), next($arguments));
        }

        $this->assertSame($expected_code, $exception->getCode());
        $this->assertSame($expected_message, $exception->getMessage());
    }

    /**
     * Provides test data for test_text()
     *
     * @return array
     */
    public function provider_text()
    {
        return array(
            array(new BootphpException('foobar'), $this->dirSeparator('BootphpException [ 0 ]: foobar ~ SYS_PATH/tests/bootphp/ExceptionTest.php [ ' . __LINE__ . ' ]')),
        );
    }

    /**
     * Tests BootphpException::text()
     *
     * @test
     * @dataProvider provider_text
     * @covers BootphpException::text
     * @param object $exception exception to test
     * @param string $expected  expected output
     */
    public function test_text($exception, $expected)
    {
        $this->assertEquals($expected, BootphpException::text($exception));
    }

}
