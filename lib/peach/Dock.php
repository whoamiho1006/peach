<?php

namespace peach;

class Dock {
    private $_Events = [];
    
    /*
     * Register a new event listener.
     * */
    function on($Event, callable $Callback) {
        if (!array_key_exists($Event, $this->_Events)) {
            $this->_Events[$Event] = [ ];
        }
        
        $this->_Events[$Event][] = $Callback;
        return $this;
    }
    
    /*
     * Unregister an event handler or all.
     * */
    function off($Event, $Callback = null) {
        if (!$Callback) {
            if (array_key_exists($Event, $this->_Events)) {
                unset($this->_Events[$Event]);
            }
        }
        
        else if (array_key_exists($Event, $this->_Events)) {
            $Pos = array_search($Callback, $this->_Events[$Event]);
            
            if ($Pos !== false) {
                array_splice($this->_Events[$Event], $Pos, 1);
            }
        }
        
        return $this;
    }
    
    /*
     * Raise an event.
     * */
    function raise($Event, ... $Args) {
        if (array_key_exists($Event, $this->_Events)) {
            $Callables = array_values($this->_Events[$Event]);
            
            while (count($Callables)) {
                $Callable = array_shift($Callables);
                call_user_func_array($Callable, $Args);
            }
        }
    }
}