<?php

/**
 * Tests Bootphp Logging API
 *
 * @group bootphp
 * @group bootphp.core
 * @group bootphp.core.logging
 *
 * @author      Tinsh <kilofox2000@gmail.com>
 * @author     Matt Button <matthew@sigswitch.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @license     http://kilofox.net/bootphp/license
 */
class Bootphp_LogTest extends Unittest_TestCase
{
    /**
     * Tests that when a new logger is created the list of messages is initially
     * empty
     *
     * @test
     * @covers Log
     */
    public function test_messages_is_initially_empty()
    {
        $logger = new Log;

        $this->assertAttributeSame([], '_messages', $logger);
    }

    /**
     * Tests that when a new logger is created the list of writers is initially
     * empty
     *
     * @test
     * @covers Log
     */
    public function test_writers_is_initially_empty()
    {
        $logger = new Log;

        $this->assertAttributeSame([], '_writers', $logger);
    }

    /**
     * Test that attaching a log writer using an array of levels adds it to the array of log writers
     *
     * @TODO Is this test too specific?
     *
     * @test
     * @covers Log::attach
     */
    public function test_attach_attaches_log_writer_and_returns_this()
    {
        $logger = new Log;
        $writer = $this->getMockForAbstractClass('Log_Writer');

        $this->assertSame($logger, $logger->attach($writer));

        $this->assertAttributeSame(
            array(spl_object_hash($writer) => array('object' => $writer, 'levels' => [])), '_writers', $logger
        );
    }

    /**
     * Test that attaching a log writer using a min/max level adds it to the array of log writers
     *
     * @TODO Is this test too specific?
     *
     * @test
     * @covers Log::attach
     */
    public function test_attach_attaches_log_writer_min_max_and_returns_this()
    {
        $logger = new Log;
        $writer = $this->getMockForAbstractClass('Log_Writer');

        $this->assertSame($logger, $logger->attach($writer, Log::NOTICE, Log::CRITICAL));

        $this->assertAttributeSame(
            array(spl_object_hash($writer) => array('object' => $writer, 'levels' => array(Log::CRITICAL, Log::ERROR, Log::WARNING, Log::NOTICE))), '_writers', $logger
        );
    }

    /**
     * When we call detach() we expect the specified log writer to be removed
     *
     * @test
     * @covers Log::detach
     */
    public function test_detach_removes_log_writer_and_returns_this()
    {
        $logger = new Log;
        $writer = $this->getMockForAbstractClass('Log_Writer');

        $logger->attach($writer);

        $this->assertSame($logger, $logger->detach($writer));

        $this->assertAttributeSame([], '_writers', $logger);
    }

}
