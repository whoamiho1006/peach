<?php

namespace peach;

class Jsontp {
    private $BaseUri;
    
    function __construct($BaseUri = null) {
        $this->BaseUri = $BaseUri;
    }
    
    private function buildUri(... $Pathes) {
        if ($this->BaseUri) {
            return rtrim($this->BaseUri, '/') . '/' . implode('/', $Pathes);
        }
        
        return implode('/', $Pathes);
    }
    
    function get($Path) {
        $Url = $this->buildUri($Path);
        
        $ch=curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $Url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $Resp = curl_exec($ch);
        curl_close($ch);
        
        return $Resp ? json_decode($Resp, true) : null;
    }
    
    function post($Path, array $Req) {
        $Url = $this->buildUri($Path);
        
        $ch=curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $Url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($Req));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: application/json', 
            'Content-Type: application/json'
        ));
        
        $Resp = curl_exec($ch);
        curl_close($ch);
        
        return $Resp ? json_decode($Resp, true) : null;
    }
}