<?php

/*
 *  SimpleDispatcher is a package that provides trivial and fast way
 *  of dispatching events for PHP projects. 
 * 
 *  @author Maciej Garycki <maciekgarycki@gmail.com>
 *  @copyrights Maciej Garycki
 */

namespace Puzzle\SimpleDispatcher;


/**
 * Dispatching events object
 * 
 * @author Maciej Garycki <maciekgarycki@gmail.com>
 * @company Puzzle Design
 * @copyrights Maciej Garycki 2013
 */
class EventDispatcher implements DispatcherInterface {
    
    protected $event_listeners = array();
    
    /**
     *
     * @var EventDispatcher[]
     */
    private static $singletons = array();
    
    
    /**
     * 
     * @return EventDispatcher
     */
    public static function getInstance () {
        $class = get_called_class(); // Delayed binding for children factories...
        if (@self::$singletons[$class] === null) {
            self::$singletons[$class] = new static();
        }
        
        return self::$singletons[$class];
    }
    
    
    
    /**
     * Executes the chain of the events
     * 
     * @param array $listeners An array of callable (in context of the dispatcher)
     *                         functions/methods
     * @param \Puzzle\SimpleDispatcher\Event $event
     */
    public function executeListeners (array $listeners, EventInterface $event) {
        
        foreach ($listeners as $listener) {
            if (!$event->isPropagationStopped()) {
                $this->call($listener, $event);
            }
        }
    }

    
    
    protected function call ($listener, EventInterface $event) {
        if (is_array($listener)) {
            $this->callArray($listener, $event); // Array of class/object and method
        } else {
            call_user_func($listener, $event); // Simply callable 
        }
    }
    
    
    
    protected function callArray ($listener, EventInterface $event) {
        if (is_object($listener[0])) {
            call_user_func(array($listener[0], $listener[1]), $event);
        } else {
            $object = new $listener[0];
            call_user_func(array($object, $listener[1]), $event);
        }
    }
    
    

    /**
     * Dispatches given event
     * 
     * @param EventInterface $event
     */
    public function dispatch (EventInterface $event) {
        $name = $event->getName();
        $listeners = $this->getListeners($name);
        $this->executeListeners($listeners, $event);
        
    }

    
    
    /**
     * 
     * @param type $name
     * @return array An array of callable (in context of the dispatcher) functions/methods
     *               that can be executed with an event as a parameter. Method
     *               EventDispatcher::executeListeners() can be used
     */
    public function getListeners ($name) {
        return @$this->event_listeners[$name] ? $this->doGetListeners($name) : array();
    }
    
    
    
    private function doGetListeners ($name) {
        $listeners = array();
        ksort($this->event_listeners[$name]);
        foreach ($this->event_listeners[$name] as $ordering_array) {
            foreach ($ordering_array as $listener) {
                $listeners[] = $listener;
            }
        }
        
        return $listeners;
    }
    
    

    /**
     * Checks if any listeners have been assigned to particular event
     * 
     * @param string $name
     * @return bool
     */
    public function hasListeners ($name) {
        return isset($this->event_listeners[$name]);
    }

    
    
    /**
     * Registers a listener for given event name on given position (ordering)
     * 
     * @param string $name
     * @param array|string|callable $callable_resource_or_string
     * @param int $ordering
     * @throws \InvalidArgumentException
     */
    public function registerListener ($name, $callable_resource_or_string, $ordering = 0) {
        $this->validateOrdering($ordering);
        $this->event_listeners[$name] = @$this->event_listeners[$name] ?: array();
        $this->event_listeners[$name][$ordering] = @$this->event_listeners[$name][$ordering] ?: array();
        
        if (is_array($callable_resource_or_string)) { // May be a 2 indexes array: class name, callable name
            $this->validateCallableArray($callable_resource_or_string);
            $listener = $callable_resource_or_string;
            
        } elseif (is_string($callable_resource_or_string)) { // May be a string that has to be converted to array
            $listener = $this->stringToArray($callable_resource_or_string);
            
        } elseif (is_callable($callable_resource_or_string)) { // May be simply a callable function
            $listener = $callable_resource_or_string;
            
        } else {
            throw new \InvalidArgumentException('Unrecognisable type of listener!');
        }
        
        $this->event_listeners[$name][$ordering][] = $listener;
    }    
    
    protected function validateOrdering ($ordering) {
        if (!is_numeric($ordering) OR !ctype_digit(strval($ordering)) OR ($ordering < 0)) {
            throw new \InvalidArgumentException("The ordering provided must be numeric and non-negative!");
        }
    }
    
    
    
    
    private function validateCallableArray (array $params) {
        $exception_string = 'Invalid array passed, first index expected to be a class name or object, second a method name!';
        if (is_object(@$params[0])) {
            if (!method_exists($params[0], @$params[1])) {
                throw new \InvalidArgumentException($exception_string);
            }
        } else {
            if (!class_exists(@$params[0]) OR !method_exists($params[0], @$params[1])) {
                throw new \InvalidArgumentException($exception_string);
            }
        }
    }
    
    
    
    private function stringToArray ($callable_name_string) {
        $callable_array = explode('::', $callable_name_string);
        try {
            $this->validateCallableArray($callable_array);
        } catch (\InvalidArgumentException $exc) {
            throw new \InvalidArgumentException('If passing a string as a listener it has to be a full class name and method name joined with \'::\'.', 500, $exc);
        }
        
        return $callable_array;
    }
}