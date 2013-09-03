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
 *  Interface providing dispatcher methods
 *
 *  @author Maciej Garycki <maciekgarycki@gmail.com>
 *  @company Puzzle Design
 *  @copyrights Maciej Garycki 2013
 */
interface DispatcherInterface {
    
    public function dispatch (EventInterface $event);
    
    public function registerListener ($name, $callable_resource_or_string, $ordering = 0);

    public function getListeners ($name);
    
    public function hasListeners ($name);
    
}
