<?php

namespace peach;

class Exec {
    private static $Instance;
    
    private $ExecName;
    private $ExecArgs;
    
    public static function exec() {
        if (self::$Instance) {
            throw new \Exception('Exec cannot begin multiple!');
        }
        
        self::$Instance = new Exec();
    }
    
    private function __construct() {
        date_default_timezone_set('UTC');
        $this->ExecName = basename(array_shift($_SERVER['argv']));
        $this->ExecArgs = $_SERVER['argv'];
    }
    
    static function hasSwitch(... $Switches) {
        foreach ($Switches as $Switch) {
            if (array_search($Switch, self::$Instance->ExecArgs) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    static function getOption(... $Switches) {
        foreach ($Switches as $Switch) {
            foreach (self::$Instance->ExecArgs as $Arg) {
                if (($p = strpos($Arg, $Switch . '=')) !== false && !$p) {
                    $R = explode('=', $Arg);
                    array_shift($R);
                    
                    return implode('=', $R);
                }
            }
        }
        
        return false;
    }
    
    static function getValues() {
        $R = [];
        
        foreach (self::$Instance->ExecArgs as $Arg) {
            if (substr($Arg, 0, 1) != '-') {
                $R[] = $Arg;
            }
        }
        
        return $R;
    }
}