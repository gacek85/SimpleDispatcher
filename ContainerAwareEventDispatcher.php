<?php


/*
 *  SimpleDispatcher is a package that provides trivial and fast way
 *  of dispatching events for PHP projects. 
 * 
 *  @author Maciej Garycki <maciekgarycki@gmail.com>
 *  @copyrights Maciej Garycki
 */

namespace Puzzle\SimpleDispatcher;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Dispatching events object
 * 
 * @author Maciej Garycki <maciekgarycki@gmail.com>
 * @company Puzzle Design
 * @copyrights Maciej Garycki 2013
 */
class ContainerAwareEventDispatcher extends EventDispatcher implements DispatcherInterface,
                                                                       ContainerAwareInterface {
    
    /**
     *
     * @var ContainerInterface
     */
    private $container = null;
    

    public function setContainer(ContainerInterface $container = null) {
        $this->container = $container;
    }

    protected function callArray($listener, EventInterface $event) {
        if ($this->isConditionMet($listener)) {
            $object = $this->container->get($listener[0]);
            $method = $listener[1];
            call_user_func(array($object, $method), $event);
            
        } else {
            parent::callArray($listener, $event);
        }
        
    }

    

    /**
     * Registers a listener for given event name on given position (ordering)
     * 
     * @param string $name
     * @param array|string|callable $callable_resource_or_string
     * @param int $ordering
     * @throws \InvalidArgumentException
     */
    public function registerListener($name, $callable_resource_or_string, $ordering = 0) {
        if ($this->isConditionMet($callable_resource_or_string)) {

            // That will be the container index...
            // Don't want to call it now to avoid instantiating loads of objects...
            if (!$this->container->has($callable_resource_or_string[0])) {
                throw new \InvalidArgumentException("Given service id does not exist: "
                        . $callable_resource_or_string[0]);
            }

            $this->validateOrdering($ordering);
            $this->event_listeners[$name][$ordering][] = $callable_resource_or_string;
            
        } else {
            parent::registerListener($name, $callable_resource_or_string, $ordering);
        }
    }
    
    
    
    private function isConditionMet ($resource) {
        return (is_array($resource) && 
               is_string(@$resource[0]) &&
               !class_exists(@$resource[0]));
    }
}
