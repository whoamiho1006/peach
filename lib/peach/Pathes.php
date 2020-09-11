<?php

namespace peach;

class Pathes {
    static function var(... $Pathes) {
        $FullPath = PEACH_ROOT . '/var/' . implode('/', $Pathes);
        
        if (!file_exists($FullPath)) {
            Shell::mkdir('-pv', $FullPath);
        }
        
        return $FullPath;
    }
    
    static function etc(... $Pathes) {
        $FullPath = PEACH_ROOT . '/etc/' . implode('/', $Pathes);
        
        if (!file_exists($FullPath)) {
            Shell::mkdir('-pv', $FullPath);
        }
        
        return $FullPath;
    }
}