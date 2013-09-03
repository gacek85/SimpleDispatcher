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
 *  Basic Event
 *
 *  @author Maciej Garycki <maciekgarycki@gmail.com>
 *  @company Puzzle Design
 *  @copyrights Maciej Garycki 2013
 */

class Event implements EventInterface {
    
    private $propagation_stopped = false;
    protected $name = null;
    protected $params = array();


    /**
     * 
     * @param string $name Mandatory event name
     */
    public function __construct($name, array $params = array()) {
        $this->name = $name;
        $this->params = $params;
    }

    /**
     * Stops the event's propagation, it will no more be dispatched
     * by any further listeners.
     * 
     * @return Event Returns this for chaining.
     */
    public function stopPropagation () {
        $this->propagation_stopped = true;
        return $this;
    }
    
    /**
     * Defines weather or not a Event::stopPropagation()
     * method has been called on this object
     * 
     * @return bool
     */
    public function isPropagationStopped () {
        return $this->propagation_stopped;
    }

    /**
     * Provides event name
     * 
     * @return string
     */
    public function getName () {
        return $this->name;
    }
    
    /**
     * Provide previously set parameter for given name
     * 
     * @param string $name
     * @throws \InvalidArgumentException
     * @return mixed
     */
    public function getParameter ($name) {
        if (!isset($this->params[$name])) {
            throw new \InvalidArgumentException('Parameter \'' . $name . '\' has not been set in this event!');
        }
        
        return $this->params[$name];
    }
    
    /**
     * Sets a value for parameter of given name
     * 
     * @param string $name
     * @param mixed $value
     */
    public function setParameter ($name, $value) {
        $this->params[$name] = $value;
    }
    
}