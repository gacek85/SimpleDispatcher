<?php

/*
 *  SimpleDispatcher is a package that provides trivial and fast way
 *  of dispatching events for PHP projects. 
 * 
 *  @author Maciej Garycki <maciekgarycki@gmail.com>
 *  @copyrights Maciej Garycki
 */

namespace Puzzle\SimpleDispatcher\Tests;

use Puzzle\SimpleDispatcher\EventInterface;
use Puzzle\SimpleDispatcher\EventDispatcher;

/**
 * PHPUnit test class for the basic Event
 */
class EventDispatcherTest extends \PHPUnit_Framework_TestCase {
    

    /**
     * @var EventDispatcher
     */
    protected $event_dispatcher;

    
    protected function setUp() {
        $this->event_dispatcher = new EventDispatcher();
    }
    
    protected function getEventMock ($name) {
        $mock = $this->getMock('\Puzzle\SimpleDispatcher\EventInterface');
        
        $map = $this->createValueMap();
        
        $mock->expects($this->any())
             ->method('getName')
             ->will($this->returnValue($name));
        $mock->expects($this->any())
             ->method('getParameter')
             ->will($this->returnValueMap($map));
        
        return $mock;
    }
    
    private function createValueMap () {
        $map = array();
        for ($i = 0; $i < 10; $i++) {
            $map['key_' . $i] = 'Value ' . $i;
        }
        
        return $map;
    }
    
    
    
    
    public function testGetInstance () {
        $instance = EventDispatcher::getInstance();
        $this->assertTrue($instance instanceof EventDispatcher);
        
        // Now checking if indeed a singleton...
        $second_instance = EventDispatcher::getInstance();
        $this->assertEquals(spl_object_hash($instance), spl_object_hash($second_instance));
    }
    
    
    /**
     * @covers \Puzzle\SimpleDispatcher\EventDispatcher::registerListener
     * @covers \Puzzle\SimpleDispatcher\EventDispatcher::hasListeners
     * @dataProvider getRegisterListeners
     */
    public function testRegisterListener ($event_name, array $listeners) {
        foreach ($listeners as $listener) {
            $this->event_dispatcher->registerListener($event_name, $listener);
        }
        
        $this->assertTrue($this->event_dispatcher->hasListeners($event_name));
    }
    
    
    /**
     * @covers \Puzzle\SimpleDispatcher\EventDispatcher::registerListener
     * @dataProvider getRegisterExceptionListeners
     * @expectedException \InvalidArgumentException
     */
    public function testRegisterListenerException ($event_name, array $listeners) {
        foreach ($listeners as $listener) {
            $this->event_dispatcher->registerListener($event_name, $listener);
        }
    }
    
    
    public function getRegisterListeners () {
        $name = $this->randomName();
        $listeners = array(
            '\Puzzle\SimpleDispatcher\Tests\ListenerStubClass::onGivenEvent', // String
            array(new ListenerStubClass(), 'onAnotherEvent'),   // Array: object, method
            array('\Puzzle\SimpleDispatcher\Tests\ListenerStubClass', 'onYetAnotherEvent'), // Array: class name, method
            function (EventInterface $event) {
                // Do smth
            }
        );
        
        return array(
            array(
                $name,
                $listeners
            )
        );
    }
    
    
    
    public function getRegisterExceptionListeners () {
        $name = $this->randomName();
        $listeners = array(
            '\Puzzle\SimpleDispatcher\Tests\ListenerStubClass-onGivenEvent', // String
            array(new ListenerStubClass(), 'onAnotherEventNonExisting'),   // Array: object, method
            array('\Puzzle\SimpleDispatcher\Tests\ListenerStubClass', 'onYetAnotherEventNonExisting'), // Array: class name, method
        );
        
        return array(
            array(
                $name,
                $listeners
            )
        );
    }
    
    
    /**
     * @covers \Puzzle\SimpleDispatcher\EventDispatcher::registerListener
     * @covers \Puzzle\SimpleDispatcher\EventDispatcher::dispatch
     * @dataProvider getRegisterListenersOrder
     */
    public function testRegisterListenerWithOrder ($event_name, array $listeners, array $propper_order) {
        foreach ($listeners as $listener_order) {
            $listener = $listener_order[0];
            $order = $listener_order[1];
            $this->event_dispatcher->registerListener($event_name, $listener, $order);
        }
        
        ListenerOrderStubClass::clear();
        $this->event_dispatcher->dispatch($this->getEventMock($event_name));
        
        $ordering_by_listeners = ListenerOrderStubClass::getOrdering();
        foreach ($propper_order as $key => $value) {
            $value_by_listeners = $ordering_by_listeners[$key];
            $this->assertEquals($value, $value_by_listeners);
        }
    }
    
    /**
     * @covers \Puzzle\SimpleDispatcher\EventDispatcher::executeListeners
     * @covers \Puzzle\SimpleDispatcher\EventDispatcher::getListeners
     * @dataProvider getRegisterListenersOrder
     */
    public function testExecuteListenersWithOrder ($event_name, array $listeners, array $propper_order) {
        foreach ($listeners as $listener_order) {
            $listener = $listener_order[0];
            $order = $listener_order[1];
            $this->event_dispatcher->registerListener($event_name, $listener, $order);
        }
        
        $event_mock = $this->getEventMock($event_name);
        $listeners = $this->event_dispatcher->getListeners($event_mock->getName());
        
        ListenerOrderStubClass::clear();
        EventDispatcher::getInstance()->executeListeners($listeners, $event_mock);
        
        $ordering_by_listeners = ListenerOrderStubClass::getOrdering();
        foreach ($propper_order as $key => $value) {
            $value_by_listeners = $ordering_by_listeners[$key];
            $this->assertEquals($value, $value_by_listeners);
        }
    }
    
    
    public function getRegisterListenersOrder () {
        $name = $this->randomName();
        $listeners = array(
            array(
                '\Puzzle\SimpleDispatcher\Tests\ListenerOrderStubClass::onGivenEvent',
                255
            ), // String
            array(
                array(new ListenerOrderStubClass(), 'onAnotherEvent'),
                30
            ),   // Array: object, method
            array(
                array('\Puzzle\SimpleDispatcher\Tests\ListenerOrderStubClass', 'onYetAnotherEvent'),
                45
            ), // Array: class name, method
            array (
                function (EventInterface $event) {
                    ListenerOrderStubClass::addCall('customCallable');
                }, // Callable
                0
            )
        );
        
        return array(
            array(
                $name,
                $listeners,
                array(
                    'customCallable',
                    'onAnotherEvent',
                    'onYetAnotherEvent',
                    'onGivenEvent'
                )
            )
        );
    }
    
    
    protected function randomName () {
        $letters = 'abcdefghijklmnopqrstuvwxyz';
        $min = 0;
        $max = mb_strlen($letters) - 1;
        $name = '';
        while (mb_strlen($name) < 10) {
            $index = mt_rand($min, $max);
            $name .= $letters[$index];
        }
        
        return $name;
    }

}

/**
 * Stub class for testing only...
 */
class ListenerStubClass {
    
    public function onGivenEvent (EventInterface $event) {
        // Do something...
    }
    
    public function onAnotherEvent (EventInterface $event) {
        // Do something...
    }
    
    public function onYetAnotherEvent (EventInterface $event) {
        // Do something...
    }
    
}


/**
 * Stub class for testing only...
 */
class ListenerOrderStubClass {
    
    private static $ordering = array();
    
    public static function clear () {
        self::$ordering = array();
    }
    
    public static function addCall ($name) {
        self::$ordering[] = $name;
    }
    
    public static function getOrdering () {
        return self::$ordering;
    }
    
    public function onGivenEvent (EventInterface $event) {
        self::addCall('onGivenEvent');
    }
    
    public function onAnotherEvent (EventInterface $event) {
        self::addCall('onAnotherEvent');
    }
    
    public function onYetAnotherEvent (EventInterface $event) {
        self::addCall('onYetAnotherEvent');
    }
    
}