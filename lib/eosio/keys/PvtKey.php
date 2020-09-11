<?php

namespace eosio\keys;
use \Tuupola\Base58;

class PvtKey extends Key {
    private $_Pub;
    
    function __construct($Key) {
        parent::__construct($Key);
    }
    
    function getPubKey() : PubKey {
        if ($this->_Pub) {
            return $this->_Pub;
        }
        
        $EC = new \Elliptic\EC('secp256k1');
        $Base58 = new \Tuupola\Base58([
            'characters' => Base58::BITCOIN
        ]);
        
        /* Private key. */
        $DecodedKey = bin2hex(substr($Base58->decode($this->Key), 1, 32));
        $PvtKey = $EC->keyFromPrivate($DecodedKey);
        
        if (!$PvtKey) {
            throw new \Exception('Invalid Private Key!');
        }
        
        /* Calculate pub-key. */
        $PubKey = join('', array_map('chr',$PvtKey->getPublic(true, true)));
        $Hash = hash('ripemd160', $PubKey);
        
        return $this->_Pub = new PubKey("EOS" . 
            $Base58->encode($PubKey . substr(hex2bin($Hash), 0, 4)));
    }
}