<?php

namespace peach;

class Shell {
    private static $Aliases = [];
    
    static function __callStatic($Name, $Arguments) {
        for ($i = 0; $i < count($Arguments); $i++) {
            $Arguments[$i] = str_replace('"', '\"', $Arguments[$i]);
            $Arguments[$i] = "\"{$Arguments[$i]}\"";
        }
        
        $Command = "{$Name} " . implode(' ', $Arguments);
        $Output = []; $ExitCode = 0;
        
        exec("$Command 2>&1", $Output, $ExitCode);
        return new Shell($Output, $ExitCode);
    }
    
    static function alias($Name, ... $Arguments) {
        if (!isset(self::$Aliases[$Name])) {
            self::$Aliases[$Name] = [];
        }
        
        self::$Aliases[$Name] = array_unique(array_merge(
            self::$Aliases[$Name], $Arguments));
    }
    
    private $_Output;
    private $_ExitCode;
    
    private $_Expects;
    
    private function __construct($Output, $ExitCode) {
        $this->_Output = $Output;
        $this->_ExitCode = $ExitCode;
        $this->_Expects = [];
    }
    
    function expects(array $Spec) : Shell {
        $SpecKeys = array_keys($Spec);
        /*
         * [ 'var' => 'REGEX' ]
         * */
        while (count($SpecKeys)) {
            $SpecKey = array_shift($SpecKeys);
            $Regex = $Spec[$SpecKey];
            
            foreach ($this->_Output as $EachLine) {
                $Matches = [];
                
                if (preg_match($Regex, $EachLine, $Matches)) {
                    if (count ($Matches) != 1) {
                        array_shift($Matches);
                    }
                    
                    $this->_Expects[$SpecKey] = trim($Matches[0]);
                    break;
                }
            }
        }
        
        return $this;
    }
    
    function __get($name) {
        if (array_key_exists($name, $this->_Expects)) {
            return $this->_Expects[$name];
        }
        
        return false;
    }
    
}