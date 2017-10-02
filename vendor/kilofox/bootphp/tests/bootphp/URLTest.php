<?php

/**
 * Tests URL
 *
 * @group bootphp
 * @group bootphp.core
 * @group bootphp.core.url
 *
 * @author      Tinsh <kilofox2000@gmail.com>
 * @author     BRMatt <matthew@sigswitch.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @license     http://kilofox.net/bootphp/license
 */
class Bootphp_URLTest extends Unittest_TestCase
{
    /**
     * Sets up the environment
     */
    // @codingStandardsIgnoreStart
    public function setUp()
    // @codingStandardsIgnoreEnd
    {
        parent::setUp();
        Core::$config->load('url')->set('trusted_hosts', array('example\.com', 'example\.org'));
    }

    /**
     * Default values for the environment, see setEnvironment
     * @var array
     */
    // @codingStandardsIgnoreStart
    protected $environmentDefault = array(
        'Core::$base_url' => '/bootphp/',
        'Core::$index_file' => 'index.php',
        'HTTP_HOST' => 'example.com',
        '_GET' => [],
    );

    // @codingStandardsIgnoreEnd
    /**
     * Provides test data for test_base()
     *
     * @return array
     */
    public function provider_base()
    {
        return array(
            // $protocol, $index, $expected, $enviroment
            // Test with different combinations of parameters for max code coverage
            array(null, false, '/bootphp/'),
            array('http', false, 'http://example.com/bootphp/'),
            array(null, true, '/bootphp/index.php/'),
            array(null, true, '/bootphp/index.php/'),
            array('http', true, 'http://example.com/bootphp/index.php/'),
            array('https', true, 'https://example.com/bootphp/index.php/'),
            array('ftp', true, 'ftp://example.com/bootphp/index.php/'),
            // Test for automatic protocol detection, protocol = true
            array(true, true, 'cli://example.com/bootphp/index.php/', array('HTTPS' => false, 'Request::$initial' => Request::factory('/')->protocol('cli'))),
            // Change base url'
            array('https', false, 'https://example.com/bootphp/', array('Core::$base_url' => 'omglol://example.com/bootphp/')),
            // Use port in base url, issue #3307
            array('http', false, 'http://example.com:8080/', array('Core::$base_url' => 'example.com:8080/')),
            // Use protocol from base url if none specified
            array(null, false, 'http://www.example.com/', array('Core::$base_url' => 'http://www.example.com/')),
            // Use HTTP_HOST before SERVER_NAME
            array('http', false, 'http://example.com/bootphp/', array('HTTP_HOST' => 'example.com', 'SERVER_NAME' => 'example.org')),
            // Use SERVER_NAME if HTTP_HOST DNX
            array('http', false, 'http://example.org/bootphp/', array('HTTP_HOST' => null, 'SERVER_NAME' => 'example.org')),
        );
    }

    /**
     * Tests URL::base()
     *
     * @test
     * @dataProvider provider_base
     * @param boolean $protocol    Parameter for Url::base()
     * @param boolean $index       Parameter for Url::base()
     * @param string  $expected    Expected url
     * @param array   $enviroment  Array of enviroment vars to change @see Bootphp_URLTest::setEnvironment()
     */
    public function test_base($protocol, $index, $expected, array $enviroment = [])
    {
        $this->setEnvironment($enviroment);

        $this->assertSame(
            $expected, URL::base($protocol, $index)
        );
    }

    /**
     * Provides test data for test_site()
     *
     * @return array
     */
    public function provider_site()
    {
        return array(
            array('', null, '/bootphp/index.php/'),
            array('', 'http', 'http://example.com/bootphp/index.php/'),
            array('my/site', null, '/bootphp/index.php/my/site'),
            array('my/site', 'http', 'http://example.com/bootphp/index.php/my/site'),
            // @ticket #3110
            array('my/site/page:5', null, '/bootphp/index.php/my/site/page:5'),
            array('my/site/page:5', 'http', 'http://example.com/bootphp/index.php/my/site/page:5'),
            array('my/site?var=asd&bootphp=awesome', null, '/bootphp/index.php/my/site?var=asd&bootphp=awesome'),
            array('my/site?var=asd&bootphp=awesome', 'http', 'http://example.com/bootphp/index.php/my/site?var=asd&bootphp=awesome'),
            array('?bootphp=awesome&life=good', null, '/bootphp/index.php/?bootphp=awesome&life=good'),
            array('?bootphp=awesome&life=good', 'http', 'http://example.com/bootphp/index.php/?bootphp=awesome&life=good'),
            array('?bootphp=awesome&life=good#fact', null, '/bootphp/index.php/?bootphp=awesome&life=good#fact'),
            array('?bootphp=awesome&life=good#fact', 'http', 'http://example.com/bootphp/index.php/?bootphp=awesome&life=good#fact'),
            array('some/long/route/goes/here?bootphp=awesome&life=good#fact', null, '/bootphp/index.php/some/long/route/goes/here?bootphp=awesome&life=good#fact'),
            array('some/long/route/goes/here?bootphp=awesome&life=good#fact', 'http', 'http://example.com/bootphp/index.php/some/long/route/goes/here?bootphp=awesome&life=good#fact'),
            array('/route/goes/here?bootphp=awesome&life=good#fact', 'https', 'https://example.com/bootphp/index.php/route/goes/here?bootphp=awesome&life=good#fact'),
            array('/route/goes/here?bootphp=awesome&life=good#fact', 'ftp', 'ftp://example.com/bootphp/index.php/route/goes/here?bootphp=awesome&life=good#fact'),
        );
    }

    /**
     * Tests URL::site()
     *
     * @test
     * @dataProvider provider_site
     * @param string          $uri         URI to use
     * @param boolean|string  $protocol    Protocol to use
     * @param string          $expected    Expected result
     * @param array           $enviroment  Array of enviroment vars to set
     */
    public function test_site($uri, $protocol, $expected, array $enviroment = [])
    {
        $this->setEnvironment($enviroment);

        $this->assertSame(
            $expected, URL::site($uri, $protocol)
        );
    }

    /**
     * Provides test data for test_site_url_encode_uri()
     * See issue #2680
     *
     * @return array
     */
    public function provider_site_url_encode_uri()
    {
        $provider = array(
            array('test', 'encode'),
            array('test', 'éñçø∂ë∂'),
            array('†éß†', 'encode'),
            array('†éß†', 'éñçø∂ë∂', 'µåñ¥'),
        );

        foreach ($provider as $i => $params) {
            // Every non-ASCII character except for forward slash should be encoded...
            $expected = implode('/', array_map('rawurlencode', $params));

            // ... from a URI that is not encoded
            $uri = implode('/', $params);

            $provider[$i] = array("/bootphp/index.php/{$expected}", $uri);
        }

        return $provider;
    }

    /**
     * Tests URL::site for proper URL encoding when working with non-ASCII characters.
     *
     * @test
     * @dataProvider provider_site_url_encode_uri
     */
    public function test_site_url_encode_uri($expected, $uri)
    {
        $this->assertSame($expected, URL::site($uri, false));
    }

    /**
     * Provides test data for test_title()
     * @return array
     */
    public function provider_title()
    {
        return array(
            // Tests that..
            // Title is converted to lowercase
            array('we-shall-not-be-moved', 'WE SHALL NOT BE MOVED', '-'),
            // Excessive white space is removed and replaced with 1 char
            array('thissssss-is-it', 'THISSSSSS         IS       IT  ', '-'),
            // separator is either - (dash) or _ (underscore) & others are converted to underscores
            array('some-title', 'some title', '-'),
            array('some_title', 'some title', '_'),
            array('some!title', 'some title', '!'),
            array('some:title', 'some title', ':'),
            // Numbers are preserved
            array('99-ways-to-beat-apple', '99 Ways to beat apple', '-'),
            // ... with lots of spaces & caps
            array('99_ways_to_beat_apple', '99    ways   TO beat      APPLE', '_'),
            array('99-ways-to-beat-apple', '99    ways   TO beat      APPLE', '-'),
            // Invalid characters are removed
            array('each-gbp-is-now-worth-32-usd', 'Each GBP(£) is now worth 32 USD($)', '-'),
            // ... inc. separator
            array('is-it-reusable-or-re-usable', 'Is it reusable or re-usable?', '-'),
            // Doing some crazy UTF8 tests
            array('espana-wins', 'España-wins', '-', true),
        );
    }

    /**
     * Tests URL::title()
     *
     * @test
     * @dataProvider provider_title
     * @param string $title        Input to convert
     * @param string $separator    Seperate to replace invalid characters with
     * @param string $expected     Expected result
     */
    public function test_title($expected, $title, $separator, $ascii_only = false)
    {
        $this->assertSame(
            $expected, URL::title($title, $separator, $ascii_only)
        );
    }

    /**
     * Provides test data for URL::query()
     * @return array
     */
    public function provider_query()
    {
        return array(
            array([], '', null),
            array(array('_GET' => array('test' => 'data')), '?test=data', null),
            array([], '?test=data', array('test' => 'data')),
            array(array('_GET' => array('more' => 'data')), '?more=data&test=data', array('test' => 'data')),
            array(array('_GET' => array('sort' => 'down')), '?test=data', array('test' => 'data'), false),
            // http://dev.kilofox.net/issues/3362
            array([], '', array('key' => null)),
            array([], '?key=0', array('key' => false)),
            array([], '?key=1', array('key' => true)),
            array(array('_GET' => array('sort' => 'down')), '?sort=down&key=1', array('key' => true)),
            array(array('_GET' => array('sort' => 'down')), '?sort=down&key=0', array('key' => false)),
            // @issue 4240
            array(array('_GET' => array('foo' => array('a' => 100))), '?foo%5Ba%5D=100&foo%5Bb%5D=bar', array('foo' => array('b' => 'bar'))),
            array(array('_GET' => array('a' => 'a')), '?a=b', array('a' => 'b')),
        );
    }

    /**
     * Tests URL::query()
     *
     * @test
     * @dataProvider provider_query
     * @param array $enviroment Set environment
     * @param string $expected Expected result
     * @param array $params Query string
     * @param boolean $use_get Combine with GET parameters
     */
    public function test_query($enviroment, $expected, $params, $use_get = true)
    {
        $this->setEnvironment($enviroment);

        $this->assertSame(
            $expected, URL::query($params, $use_get)
        );
    }

    /**
     * Provides test data for URL::is_trusted_host()
     * @return array
     */
    public function provider_is_trusted_host()
    {
        return array(
            // data set #0
            array(
                'givenhost',
                array(
                    'list-of-trusted-hosts',
                ),
                false
            ),
            // data set #1
            array(
                'givenhost',
                array(
                    'givenhost',
                    'example\.com',
                ),
                true
            ),
            // data set #2
            array(
                'www.kilofox.net',
                array(
                    '.*\.bootphpframework\.org',
                ),
                true
            ),
            // data set #3
            array(
                'kilofox.net',
                array(
                    '.*\.bootphpframework\.org',
                ),
                false // because we are requesting a subdomain
            ),
        );
    }

    /**
     * Tests URL::is_trusted_hosts()
     *
     * @test
     * @dataProvider provider_is_trusted_host
     * @param string $host the given host
     * @param array $trusted_hosts list of trusted hosts
     * @param boolean $expected true if host is trusted, false otherwise
     */
    public function test_is_trusted_host($host, $trusted_hosts, $expected)
    {
        $this->assertSame(
            $expected, URL::is_trusted_host($host, $trusted_hosts)
        );
    }

    /**
     * Tests if invalid host throws "Invalid host" exception
     *
     * @test
     * @expectedException BootphpException
     * @expectedExceptionMessage Invalid host <invalid>
     */
    public function test_if_invalid_host_throws_exception()
    {
        // set the global HTTP_HOST to <invalid>
        $_SERVER['HTTP_HOST'] = '<invalid>';
        // trigger exception
        URL::base('https');
    }

    /**
     * Tests if untrusted host throws "Untrusted host" exception
     *
     * @test
     * @expectedException BootphpException
     * @expectedExceptionMessage Untrusted host untrusted.com
     */
    public function test_if_untrusted_host_throws_exception()
    {
        // set the global HTTP_HOST to a valid but untrusted host
        $_SERVER['HTTP_HOST'] = 'untrusted.com';
        // trigger exception
        URL::base('https');
    }

}
