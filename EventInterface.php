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
 *  Event Abstraction definition
 *
 *  @author Maciej Garycki <maciekgarycki@gmail.com>
 *  @company Puzzle Design
 *  @copyrights Maciej Garycki 2013
 */

interface EventInterface {
    

    /**
     * Stops the event's propagation, it will no more be dispatched
     * by any further listeners.
     * 
     * @return Event Returns this for chaining.
     */
    public function stopPropagation ();
    
    /**
     * Defines weather or not a Event::stopPropagation()
     * method has been called on this object
     * 
     * @return bool
     */
    public function isPropagationStopped ();

    /**
     * Provides event name
     * 
     * @return string
     */
    public function getName ();
    
    /**
     * Provide previously set parameter for given name
     * 
     * @param string $name
     * @return mixed
     */
    public function getParameter ($name);
    
    /**
     * Sets a value for parameter of given name
     * 
     * @param string $name
     * @param mixed $value
     */
    public function setParameter ($name, $value);
    
}