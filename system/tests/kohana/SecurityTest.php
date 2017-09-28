<?php

/**
 * Tests Bootphp_Security
 *
 * @group bootphp
 * @group bootphp.core
 * @group bootphp.core.security
 *
 */
class Bootphp_SecurityTest extends Unittest_TestCase
{
    /**
     * Provides test data for test_envode_php_tags()
     *
     * @return array Test data sets
     */
    public function provider_encode_php_tags()
    {
        return array(
            array("&lt;?php echo 'helloo'; ?&gt;", "<?php echo 'helloo'; ?>"),
        );
    }

    /**
     * Tests Security::encode_php_tags()
     *
     * @test
     * @dataProvider provider_encode_php_tags
     * @covers Security::encode_php_tags
     */
    public function test_encode_php_tags($expected, $input)
    {
        $this->assertSame($expected, Security::encode_php_tags($input));
    }

    /**
     * Provides test data for test_strip_image_tags()
     *
     * @return array Test data sets
     */
    public function provider_strip_image_tags()
    {
        return array(
            array('foo', '<img src="foo" />'),
        );
    }

    /**
     * Tests Security::strip_image_tags()
     *
     * @test
     * @dataProvider provider_strip_image_tags
     * @covers Security::strip_image_tags
     */
    public function test_strip_image_tags($expected, $input)
    {
        $this->assertSame($expected, Security::strip_image_tags($input));
    }

    /**
     * Provides test data for Security::token()
     *
     * @return array Test data sets
     */
    public function provider_csrf_token()
    {
        $array = [];
        for ($i = 0; $i <= 4; $i++) {
            Security::$token_name = 'token_' . $i;
            $array[] = array(Security::token(true), Security::check(Security::token(false)), $i);
        }
        return $array;
    }

    /**
     * Tests Security::token()
     *
     * @test
     * @dataProvider provider_csrf_token
     * @covers Security::token
     */
    public function test_csrf_token($expected, $input, $iteration)
    {
        //@todo: the Security::token tests need to be reviewed to check how much of the logic they're actually covering
        Security::$token_name = 'token_' . $iteration;
        $this->assertSame(true, $input);
        $this->assertSame($expected, Security::token(false));
        Session::instance()->delete(Security::$token_name);
    }

}
