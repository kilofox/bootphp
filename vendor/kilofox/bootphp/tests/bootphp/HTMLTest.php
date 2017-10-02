<?php

/**
 * Tests HTML
 *
 * @group bootphp
 * @group bootphp.core
 * @group bootphp.core.html
 *
 * @author      Tinsh <kilofox2000@gmail.com>
 * @author     BRMatt <matthew@sigswitch.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @license     http://kilofox.net/bootphp/license
 */
class Bootphp_HTMLTest extends Unittest_TestCase
{
    /**
     * Sets up the environment
     */
    // @codingStandardsIgnoreStart
    public function setUp()
    // @codingStandardsIgnoreEnd
    {
        parent::setUp();
        Core::$config->load('url')->set('trusted_hosts', array('www\.bootphpframework\.org'));
    }

    /**
     * Defaults for this test
     * @var array
     */
    // @codingStandardsIgnoreStart
    protected $environmentDefault = array(
        'Core::$base_url' => '/bootphp/',
        'Core::$index_file' => 'index.php',
        'HTML::$strict' => true,
        'HTTP_HOST' => 'www.kilofox.net',
    );

    // @codingStandardsIgnoreStart
    /**
     * Provides test data for test_attributes()
     *
     * @return array
     */
    public function provider_attributes()
    {
        return array(
            array(
                array('name' => 'field', 'random' => 'not_quite', 'id' => 'unique_field'),
                [],
                ' id="unique_field" name="field" random="not_quite"'
            ),
            array(
                array('invalid' => null),
                [],
                ''
            ),
            array(
                [],
                [],
                ''
            ),
            array(
                array('name' => 'field', 'checked'),
                [],
                ' name="field" checked="checked"',
            ),
            array(
                array('id' => 'disabled_field', 'disabled'),
                array('HTML::$strict' => false),
                ' id="disabled_field" disabled',
            ),
        );
    }

    /**
     * Tests HTML::attributes()
     *
     * @test
     * @dataProvider provider_attributes
     * @param array  $attributes  Attributes to use
     * @param array  $options     Environment options to use
     * @param string $expected    Expected output
     */
    public function test_attributes(array $attributes, array $options, $expected)
    {
        $this->setEnvironment($options);

        $this->assertSame(
            $expected, HTML::attributes($attributes)
        );
    }

    /**
     * Provides test data for test_script
     *
     * @return array Array of test data
     */
    public function provider_script()
    {
        return array(
            array(
                '<script type="text/javascript" src="http://google.com/script.js"></script>',
                'http://google.com/script.js',
            ),
            array(
                '<script type="text/javascript" src="http://www.kilofox.net/bootphp/index.php/my/script.js"></script>',
                'my/script.js',
                null,
                'http',
                true
            ),
            array(
                '<script type="text/javascript" src="https://www.kilofox.net/bootphp/my/script.js"></script>',
                'my/script.js',
                null,
                'https',
                false
            ),
            array(
                '<script type="text/javascript" src="https://www.kilofox.net/bootphp/my/script.js"></script>',
                '/my/script.js', // Test absolute paths
                null,
                'https',
                false
            ),
            array(
                '<script type="text/javascript" src="//google.com/script.js"></script>',
                '//google.com/script.js',
            ),
        );
    }

    /**
     * Tests HTML::script()
     *
     * @test
     * @dataProvider  provider_script
     * @param string  $expected       Expected output
     * @param string  $file           URL to script
     * @param array   $attributes     HTML attributes for the anchor
     * @param string  $protocol       Protocol to use
     * @param bool    $index          Should the index file be included in url?
     */
    public function test_script($expected, $file, array $attributes = null, $protocol = null, $index = false)
    {
        $this->assertSame(
            $expected, HTML::script($file, $attributes, $protocol, $index)
        );
    }

    /**
     * Data provider for the style test
     *
     * @return array Array of test data
     */
    public function provider_style()
    {
        return array(
            array(
                '<link type="text/css" href="http://google.com/style.css" rel="stylesheet" />',
                'http://google.com/style.css',
                [],
                null,
                false
            ),
            array(
                '<link type="text/css" href="/bootphp/my/style.css" rel="stylesheet" />',
                'my/style.css',
                [],
                null,
                false
            ),
            array(
                '<link type="text/css" href="https://www.kilofox.net/bootphp/my/style.css" rel="stylesheet" />',
                'my/style.css',
                [],
                'https',
                false
            ),
            array(
                '<link type="text/css" href="https://www.kilofox.net/bootphp/index.php/my/style.css" rel="stylesheet" />',
                'my/style.css',
                [],
                'https',
                true
            ),
            array(
                '<link type="text/css" href="https://www.kilofox.net/bootphp/index.php/my/style.css" rel="stylesheet" />',
                '/my/style.css',
                [],
                'https',
                true
            ),
            array(
                // #4283: http://dev.kilofox.net/issues/4283
                '<link type="text/css" href="https://www.kilofox.net/bootphp/index.php/my/style.css" rel="stylesheet/less" />',
                'my/style.css',
                array(
                    'rel' => 'stylesheet/less'
                ),
                'https',
                true
            ),
            array(
                '<link type="text/css" href="//google.com/style.css" rel="stylesheet" />',
                '//google.com/style.css',
                [],
                null,
                false
            ),
        );
    }

    /**
     * Tests HTML::style()
     *
     * @test
     * @dataProvider  provider_style
     * @param string  $expected     The expected output
     * @param string  $file         The file to link to
     * @param array   $attributes   Any extra attributes for the link
     * @param string  $protocol     Protocol to use
     * @param bool    $index        Whether the index file should be added to the link
     */
    public function test_style($expected, $file, array $attributes = null, $protocol = null, $index = false)
    {
        $this->assertSame(
            $expected, HTML::style($file, $attributes, $protocol, $index)
        );
    }

    /**
     * Provides test data for test_anchor
     *
     * @return array Test data
     */
    public function provider_anchor()
    {
        return array(
            // a fragment-only anchor
            array(
                '<a href="#go-to-section-bootphp">Bootphp</a>',
                [],
                '#go-to-section-bootphp',
                'Bootphp',
            ),
            // a query-only anchor
            array(
                '<a href="?cat=a">Category A</a>',
                [],
                '?cat=a',
                'Category A',
            ),
            array(
                '<a href="http://kilofox.net">Bootphp</a>',
                [],
                'http://kilofox.net',
                'Bootphp',
            ),
            array(
                '<a href="http://google.com" target="_blank">GOOGLE</a>',
                [],
                'http://google.com',
                'GOOGLE',
                array('target' => '_blank'),
                'http',
            ),
            array(
                '<a href="https://www.kilofox.net/bootphp/users/example">Bootphp</a>',
                [],
                'users/example',
                'Bootphp',
                null,
                'https',
                false,
            ),
            array(
                '<a href="https://www.kilofox.net/bootphp/index.php/users/example">Bootphp</a>',
                [],
                'users/example',
                'Bootphp',
                null,
                'https',
                true,
            ),
            array(
                '<a href="https://www.kilofox.net/bootphp/index.php/users/example">Bootphp</a>',
                [],
                'users/example',
                'Bootphp',
                null,
                'https',
            ),
            array(
                '<a href="https://www.kilofox.net/bootphp/index.php/users/example">Bootphp</a>',
                [],
                'users/example',
                'Bootphp',
                null,
                'https',
                true,
            ),
            array(
                '<a href="https://www.kilofox.net/bootphp/users/example">Bootphp</a>',
                [],
                'users/example',
                'Bootphp',
                null,
                'https',
                false,
            ),
            array(
                '<a href="https://www.kilofox.net/bootphp/users/example">Bootphp</a>',
                [],
                '/users/example',
                'Bootphp',
                null,
                'https',
                false,
            ),
        );
    }

    /**
     * Tests HTML::anchor
     *
     * @test
     * @dataProvider provider_anchor
     */
    public function test_anchor($expected, array $options, $uri, $title = null, array $attributes = null, $protocol = null, $index = true)
    {
        // $this->setEnvironment($options);

        $this->assertSame(
            $expected, HTML::anchor($uri, $title, $attributes, $protocol, $index)
        );
    }

    /**
     * Data provider for test_file_anchor
     *
     * @return array
     */
    public function provider_file_anchor()
    {
        return array(
            array(
                '<a href="/bootphp/mypic.png">My picture file</a>',
                [],
                'mypic.png',
                'My picture file',
            ),
            array(
                '<a href="https://www.kilofox.net/bootphp/index.php/mypic.png" attr="value">My picture file</a>',
                array('attr' => 'value'),
                'mypic.png',
                'My picture file',
                'https',
                true
            ),
            array(
                '<a href="ftp://www.kilofox.net/bootphp/mypic.png">My picture file</a>',
                [],
                'mypic.png',
                'My picture file',
                'ftp',
                false
            ),
            array(
                '<a href="ftp://www.kilofox.net/bootphp/mypic.png">My picture file</a>',
                [],
                '/mypic.png',
                'My picture file',
                'ftp',
                false
            ),
        );
    }

    /**
     * Test for HTML::file_anchor()
     *
     * @test
     * @covers HTML::file_anchor
     * @dataProvider provider_file_anchor
     */
    public function test_file_anchor($expected, array $attributes, $file, $title = null, $protocol = null, $index = false)
    {
        $this->assertSame(
            $expected, HTML::file_anchor($file, $title, $attributes, $protocol, $index)
        );
    }

}
