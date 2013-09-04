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
use Puzzle\SimpleDispatcher\ContainerAwareEventDispatcher;
use Symfony\Component\DependencyInjection\ContainerBuilder;


/**
 * PHPUnit test class for the basic Event
 */
class ContainerAwareEventDispatcherTest extends EventDispatcherTest {
    
    /**
     * @var `containerAwareEventDispatcher
     */
    protected $event_dispatcher;
    
    protected function setUp() {
        $this->event_dispatcher = new ContainerAwareEventDispatcher();
        $container = new ContainerBuilder();
        $container->set('listener_service', new ListenerServiceStub());
        $this->event_dispatcher->setContainer($container);
    }
    
    
    /**
     * @covers \Puzzle\SimpleDispatcher\EventDispatcher::registerListener
     * @covers \Puzzle\SimpleDispatcher\EventDispatcher::dispatch
     * @dataProvider getRegisterListenersOrderService
     */
    public function testRegisterListenerWithOrderService ($event_name, array $listeners, array $propper_order) {
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
    
    public function getRegisterListenersOrderService () {
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
            ),
            array(
                array('listener_service', 'onEvent'),
                46
            ) // Service
        );
        
        return array(
            array(
                $name,
                $listeners,
                array(
                    'customCallable',
                    'onAnotherEvent',
                    'onYetAnotherEvent',
                    'onServiceEvent',
                    'onGivenEvent'
                )
            )
        );
    }
}

class ListenerServiceStub {
    public function onEvent (EventInterface $event) {
        ListenerOrderStubClass::addCall('onServiceEvent');
    }
}
