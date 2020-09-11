<?php

namespace eosio\keys;
use \Tuupola\Base58;

class KeyPair {
    private $Pvt, $Pub;
            
    function __construct(... $Keys) {
        foreach ($Keys as $Key) {
            if ($Key instanceof PubKey) {
                $this->Pub = $Key;
            }
            
            else if ($Key instanceof PvtKey) {
                $this->Pvt = $Key;
            }
        }
        
        if (!$this->Pub && $this->Pvt) {
            $this->Pub = $this->Pvt->getPubKey();
        }
    }
    
    static function generate() : KeyPair {
        $Exec = \peach\Shell::cleos('create', 'key', '--to-console');
        $Exec->expects([ 'pvt' => '/^Private key: ([a-zA-Z0-9]*)/' ]);

        return new KeyPair(
            new PvtKey($Exec->pvt)
        );
    }
    
    function isValid() { return $this->Pub || $this->Pvt; }
    function hasAuthority() { return $this->Pvt !== null; }
    
    function getPubKey() : PubKey { return $this->Pub; }
    function getPvtKey() : PvtKey { return $this->Pvt; }
}