<?php

/**
 * Unit tests for external request client
 *
 * @group bootphp
 * @group bootphp.request
 * @group bootphp.request.client
 * @group bootphp.request.client.external
 *
 * @author      Tinsh <kilofox2000@gmail.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @license     http://kilofox.net/bootphp/license
 */
class Bootphp_Request_Client_ExternalTest extends Unittest_TestCase
{
    /**
     * Provider for test_factory()
     *
     * @return  array
     */
    public function provider_factory()
    {
        Request_Client_External::$client = 'Request_Client_Stream';

        $return = array(
            array(
                [],
                null,
                'Request_Client_Stream'
            ),
            array(
                [],
                'Request_Client_Stream',
                'Request_Client_Stream'
            )
        );

        if (extension_loaded('curl')) {
            $return[] = array(
                [],
                'Request_Client_Curl',
                'Request_Client_Curl'
            );
        }

        if (extension_loaded('http')) {
            $return[] = array(
                [],
                'Request_Client_HTTP',
                'Request_Client_HTTP'
            );
        }

        return $return;
    }

    /**
     * Tests the [Request_Client_External::factory()] method
     *
     * @dataProvider provider_factory
     *
     * @param   array   $params  params
     * @param   string  $client  client
     * @param   Request_Client_External $expected expected
     * @return  void
     */
    public function test_factory($params, $client, $expected)
    {
        $this->assertInstanceOf($expected, Request_Client_External::factory($params, $client));
    }

    /**
     * Data provider for test_options
     *
     * @return  array
     */
    public function provider_options()
    {
        return array(
            array(
                null,
                null,
                []
            ),
            array(
                array('foo' => 'bar', 'stfu' => 'snafu'),
                null,
                array('foo' => 'bar', 'stfu' => 'snafu')
            ),
            array(
                'foo',
                'bar',
                array('foo' => 'bar')
            ),
            array(
                array('foo' => 'bar'),
                'foo',
                array('foo' => 'bar')
            )
        );
    }

    /**
     * Tests the [Request_Client_External::options()] method
     *
     * @dataProvider provider_options
     *
     * @param   mixed  $key  key
     * @param   mixed  $value  value
     * @param   array  $expected  expected
     * @return  void
     */
    public function test_options($key, $value, $expected)
    {
        // Create a mock external client
        $client = new Request_Client_Stream;

        $client->options($key, $value);
        $this->assertSame($expected, $client->options());
    }

    /**
     * Data provider for test_execute
     *
     * @return  array
     */
    public function provider_execute()
    {
        $json = '{"foo": "bar", "snafu": "stfu"}';
        $post = array('foo' => 'bar', 'snafu' => 'stfu');

        return array(
            array(
                'application/json',
                $json,
                [],
                array(
                    'content-type' => 'application/json',
                    'body' => $json
                )
            ),
            array(
                'application/json',
                $json,
                $post,
                array(
                    'content-type' => 'application/x-www-form-urlencoded; charset=' . Core::$charset,
                    'body' => http_build_query($post, null, '&')
                )
            )
        );
    }

    /**
     * Tests the [Request_Client_External::_send_message()] method
     *
     * @dataProvider provider_execute
     *
     * @return  void
     */
    public function test_execute($content_type, $body, $post, $expected)
    {
        $old_request = Request::$initial;
        Request::$initial = true;

        // Create a mock Request
        $request = new Request('http://kilofox.net/');
        $request->method('POST')
            ->headers('content-type', $content_type)
            ->body($body)
            ->post($post);

        $client = $this->getMock('Request_Client_External', array('_send_message'));
        $client->expects($this->once())
            ->method('_send_message')
            ->with($request)
            ->will($this->returnValue($this->getMock('Response')));

        $request->client($client);

        $this->assertInstanceOf('Response', $request->execute());
        $this->assertSame($expected['body'], $request->body());
        $this->assertSame($expected['content-type'], (string) $request->headers('content-type'));

        Request::$initial = $old_request;
    }

}
