<?php

namespace eosio;

class Keosd {
    private static $_Keosd;
    
    static function get() : Keosd {
        if (!self::$_Keosd) {
            self::$_Keosd = new Keosd();
        }
        
        return self::$_Keosd;
    }
    
    static function getPort() {
        $Keos = \peach\Config::load('keos');
        
        if (!isset($Keos->port)) {
            $Keos->port = 8000;
        }
        
        return $Keos->port;
    }
    
    static function setPort($Port) {
        $Keos = \peach\Config::load('keos');
        $Keos->port = $Port;
    }

    private $_Directory;
    private $_Endpoint;

    private function __construct() {
        $this->_Directory = \peach\Pathes::var('keosd');
        $this->_Endpoint = '0.0.0.0:' . self::getPort();
    }
    
    function makeLocalUrl() {
        return 'http://127.0.0.1:' . self::getPort();
    }
    
    function isAlive() {
        $PidFile = "{$this->_Directory}/keosd.pid";
        
        if (file_exists($PidFile)) {
            $Pid = trim(file_get_contents($PidFile));
            
            if ($Pid && file_exists("/proc/$Pid")) {
                return true;
            }
        }
        
        return false;
    }
    
    function start() {
        if (!$this->isAlive()) {
            $PeachRoot = $this->_Directory;
            $Endpoint = $this->_Endpoint;
            
            $ExecuteFile =  "{$PeachRoot}/keosd.sh";
            $ExecuteSh = include_once(__DIR__ . '/prefabs/keosd.sh.php');

            file_put_contents($ExecuteFile, $ExecuteSh);
            \peach\Shell::chmod('+x', $ExecuteFile);

            exec("$ExecuteFile 2>&1 1>/dev/null &");
            usleep(1000);
            return $this->isAlive();
        }
        
        return false;
    }
    
    function stop() {
        if ($this->isAlive()) {            
            $PidFile = "{$this->_Directory}/keosd.pid";
            
            $Pid = trim(file_get_contents($PidFile));
            posix_kill(intval($Pid), SIGTERM);
            
            usleep(1000);
            return !$this->isAlive();
        }
        
        return false;
    }
}