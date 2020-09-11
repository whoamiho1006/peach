<?php

namespace peach;

class Config {
    private static $Instances = [];
    private $_Properties = [];
    private $_FullPath;
    private $_HasChanges;
    
    static function load($Config) : Config {
        if (isset(self::$Instances[$Config])) {
            return self::$Instances[$Config];
        }
        
        return new Config(Pathes::etc() . "/{$Config}.conf");
    }
    
    private function __construct($FilePath) {
        $BaseDir = dirname($FilePath);
        
        if (!file_exists($BaseDir)) {
            Shell::mkdir('-pv', $BaseDir);
        }
        
        if (file_exists($FilePath)) {
            $this->_Properties = json_decode(
                file_get_contents($FilePath), true);
        }
        
        $this->_HasChanges = false;
        if (!is_array($this->_Properties)) {
            $this->_HasChanges = true;
            $this->_Properties = [];
        }
        
        $this->_FullPath = $FilePath;
    }
    
    function __destruct() {
        if ($this->_HasChanges) {
            file_put_contents($this->_FullPath, json_encode($this->_Properties,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        }
    }
    
    function __isset($Name) {
        return isset($this->_Properties[$Name]);
    }
    
    function __unset($Name) {
        unset($this->_Properties[$Name]);
        $this->_HasChanges = true;
    }
    
    function __get($Name) {
        if (isset($this->_Properties[$Name])) {
            return $this->_Properties[$Name];
        }
        
        return null;
    }
    
    function __set($Name, $Value) {
        $this->_Properties[$Name] = $Value;
        $this->_HasChanges = true;
    }
}