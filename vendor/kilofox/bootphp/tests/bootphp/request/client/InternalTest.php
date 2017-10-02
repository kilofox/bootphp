<?php

/**
 * Unit tests for internal request client
 *
 * @group bootphp
 * @group bootphp.core
 * @group bootphp.core.request
 * @group bootphp.core.request.client
 * @group bootphp.core.request.client.internal
 *
 * @author      Tinsh <kilofox2000@gmail.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @license     http://kilofox.net/bootphp/license
 */
class Bootphp_Request_Client_InternalTest extends Unittest_TestCase
{
    protected $_log_object;

    // @codingStandardsIgnoreStart
    public function setUp()
    // @codingStandardsIgnoreEnd
    {
        parent::setUp();

        // temporarily save $log object
        $this->_log_object = Core::$log;
        Core::$log = null;
    }

    // @codingStandardsIgnoreStart
    public function tearDown()
    // @codingStandardsIgnoreEnd
    {
        // re-assign log object
        Core::$log = $this->_log_object;

        parent::tearDown();
    }

    public function provider_response_failure_status()
    {
        return array(
            array('', 'Welcome', 'missing_action', 'Welcome/missing_action', 404),
            array('bootphp3', 'missing_controller', 'index', 'bootphp3/missing_controller/index', 404),
            array('', 'Template', 'missing_action', 'bootphp3/Template/missing_action', 500),
        );
    }

    /**
     * Tests for correct exception messages
     *
     * @test
     * @dataProvider provider_response_failure_status
     *
     * @return null
     */
    public function test_response_failure_status($directory, $controller, $action, $uri, $expected)
    {
        // Mock for request object
        $request = $this->getMock('Request', array('directory', 'controller', 'action', 'uri', 'response', 'method'), array($uri));

        $request->expects($this->any())
            ->method('directory')
            ->will($this->returnValue($directory));

        $request->expects($this->any())
            ->method('controller')
            ->will($this->returnValue($controller));

        $request->expects($this->any())
            ->method('action')
            ->will($this->returnValue($action));

        $request->expects($this->any())
            ->method('uri')
            ->will($this->returnValue($uri));

        $request->expects($this->any())
            ->method('response')
            ->will($this->returnValue($this->getMock('Response')));

        // mock `method` method to avoid fatals in newer versions of PHPUnit
        $request->expects($this->any())
            ->method('method')
            ->withAnyParameters();

        $internal_client = new Request_Client_Internal;

        $response = $internal_client->execute($request);

        $this->assertSame($expected, $response->status());
    }

}
