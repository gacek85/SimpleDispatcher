<?php

/*
 *  SimpleDispatcher is a package that provides trivial and fast way
 *  of dispatching events for PHP projects. 
 * 
 *  @author Maciej Garycki <maciekgarycki@gmail.com>
 *  @copyrights Maciej Garycki
 */

namespace Puzzle\SimpleDispatcher\Tests;

use Puzzle\SimpleDispatcher\Event;
use Puzzle\SimpleDispatcher\EventDispatcher;

/**
 * PHPUnit test class for the basic Event
 */
class EventTest extends \PHPUnit_Framework_TestCase {
    
    const EVENT_NAME = 'foo.bar';

    /**
     * @var Event
     */
    private $event;

    /**
     * @var EventDispatcher
     */
    private $event_dispatcher;

    
    protected function setUp() {
        $this->event = new Event(self::EVENT_NAME);
        $this->event_dispatcher = new EventDispatcher();
    }

    /**
     * @covers \Puzzle\SimpleDispatcher\Event::stopPropagation
     * @covers \Puzzle\SimpleDispatcher\Event::isPropagationStopped
     */
    public function testIsPropagationStopped() {
        $this->assertFalse($this->event->isPropagationStopped());
        $this->event->stopPropagation();
        $this->assertTrue($this->event->isPropagationStopped());
    }


    public function testGetName() {
        $this->assertEquals(self::EVENT_NAME, $this->event->getName());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetParameterException () {
        $this->event->getParameter('nonexisting_parameter');
    }
    
    /**
     * 
     * @dataProvider getParameterData
     * @param \Puzzle\SimpleDispatcher\Event $event
     * @param array $parameters Array of mixed param_name -> value
     */
    public function testGetParameter(Event $event, $parameters) {
        foreach ($parameters as $name => $parameter) {
            $parameter_from_event = $event->getParameter($name);
            $this->assertSame($parameter, $parameter_from_event);
        }
    }
    
    /**
     * 
     * @dataProvider getParameterData
     * @param \Puzzle\SimpleDispatcher\Event $event
     * @param array $parameters Array of mixed param_name -> value
     */
    public function testSetParameter (Event $event_dummy, $parameters) {
        foreach ($parameters as $name => $parameter) {
            $this->event->setParameter($name, $parameter);
            $parameter_from_event = $this->event->getParameter($name);
            $this->assertSame($parameter, $parameter_from_event);
        }
    }
    
    public function getParameterData () {
        $params = array(
            'stdO' => new \stdClass(),
            'someText' => 'Some text',
            'thisTest' => $this,
            'someArray' => array(
                'key1' => 'Val 1'
            )
        );
        
        return array(
            array(
                new Event(self::EVENT_NAME, $params),
                $params
            ),
        );
    }

}