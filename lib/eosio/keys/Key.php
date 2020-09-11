<?php

namespace eosio\keys;

/*
 * EOSIO Key.
 * */
abstract class Key {
    protected $Key;
    
    function __construct($Key) {
        $this->Key = $Key;
    }
    
    function __toString() {
        return $this->Key;
    }
    
    function isPvt() { return $this instanceof PvtKey; }
    function isPub() { return $this instanceof PubKey; }
}

