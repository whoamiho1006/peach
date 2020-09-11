<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace eosio;

/**
 * Description of Nodeos
 *
 * @author j94k
 */
class Nodeos {
    static function getAll() {
        $Nodes = \peach\Config::load('nodes');
        $OutNodeos = [];
        
        if (isset($Nodes->nodeos)) {
            $ConfiguredNodeos = $Nodes->nodeos;
            foreach ($ConfiguredNodeos as $Name => $Each) {
                $OutNodeos[] = new \eosio\Nodeos(
                    $Each['port'], $Name, $Each['host']);
            }
        }
        
        return $OutNodeos;
    }
    
    static function getNode($NodeName) : Nodeos {
        $Nodeos = self::getAll();
        foreach ($Nodeos as $Node) {
            if ($Node->getName() == $NodeName) {
                return $Node;
            }
        }
        
        return null;
    }
    
    static function add($NodeName, $PortNum, $HostName) {
        $Nodes = \peach\Config::load('nodes');
        $ConfiguredNodeos = [];
        
        if (isset($Nodes->nodeos)) {
            $ConfiguredNodeos = $Nodes->nodeos;
        }
        
        $ConfiguredNodeos[$NodeName] = [
            'port' => $PortNum,
            'host' => $HostName
        ];
        
        $Nodes->nodeos = $ConfiguredNodeos;
    }
    
    static function remove($NodeName) {
        $Nodes = \peach\Config::load('nodes');       
        
        if (isset($Nodes->nodeos)) {
            $ConfiguredNodeos = $Nodes->nodeos;

            unset($ConfiguredNodeos[$NodeName]);
            $Nodes->nodeos = $ConfiguredNodeos;
        }
    }
    
    static function setInitKey(keys\PubKey $PubKey) {
        $Nodes = \peach\Config::load('nodes');
        $Nodes->init_key = "{$PubKey}";
    }
    
    static function getInitKey() {
        $Nodes = \peach\Config::load('nodes');
        return $Nodes->init_key;
    }
    
    private $Port;
    private $P2P;
    private $Name, $Host;
    
    private $Peers = [];
    private $KeyFile = [];
    
    private $KeyPair;
    
    function __construct($Port, $Name = 'eosio', $Host = '127.0.0.1') {
        $this->Port = $Port - 1000;
        $this->P2P = $Port;
        $this->Name = $Name;
        $this->Host = $Host;
        
        if (!$this->isRemote()) {
            $KeyFile = \peach\Pathes::var('nodeos', $this->Port) . '/keypair.pks';
            if (file_exists($KeyFile)) {
                $this->KeyPair = unserialize(file_get_contents($KeyFile));
            }

            else {
                $this->KeyPair = keys\KeyPair::generate();
                file_put_contents($KeyFile, serialize($this->KeyPair));
            }
        }
    }
    
    function getName() { return $this->Name; }
    
    function getKeyPair() : keys\KeyPair {
        return $this->KeyPair;
    }
    
    function addPeer(Nodeos $Peer) {
        $this->Peers[] = $Peer;
    }
    
    function isRemote() {
        return $this->Host != '127.0.0.1';
    }
    
    function getPeerAddress() {
        return $this->Host . ':' . $this->P2P;
    }
    
    function isAlive() {
        if ($this->Host != '127.0.0.1') {
            $ch = curl_init("http://{$this->Host}:{$this->Port}/v1/chain/get_info");
            
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json; charset=UTF-8',
                "User-Agent: Peach/EOSIO 1.0"
            ]);
            
            curl_setopt($ch, CURLOPT_POSTFIELDS, '[]');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_exec($ch);
            
            if (curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) {
                return false;
            }
            
            return true;
        }
        
        $NodeosPid = \peach\Pathes::var(
            'nodeos', $this->Port) . "/nodeos.pid";
        
        if (file_exists($NodeosPid)) {
            $Pid = trim(file_get_contents($NodeosPid));
            
            if ($Pid && file_exists("/proc/$Pid")) {
                return true;
            }
        }
        
        return false;
    }
    
    function start() {
        if (!$this->isAlive()) {
            if ($this->Host != '127.0.0.1') {
                return false;
            }
            
            $PeachRoot = \peach\Pathes::var('nodeos', $this->Port);
            $NodeosPid = "{$PeachRoot}/nodeos.pid";
            $Genesis =  "{$PeachRoot}/genesis.json";
            
            $HttpEp = "0.0.0.0:{$this->Port}";
            $PeerEp = "0.0.0.0:{$this->P2P}";
            $IsGenesis = false;
            
            $Peers = $this->Peers;
            $Name = $this->Name;
            $Key = $this->getKeyPair();
            
            if (!file_exists($Genesis)) {
                $GenesisFile =  "{$PeachRoot}/genesis.sh";
                $GenesisJson = include_once (__DIR__ . '/prefabs/nodeos.genesis.json.php');
                
                file_put_contents($Genesis, $GenesisJson);
                $GenesisSh = include_once(__DIR__ . '/prefabs/nodeos.genesis.sh.php');
                
                file_put_contents($GenesisFile, $GenesisSh);
                \peach\Shell::chmod('+x', $GenesisFile);
                
                exec("$GenesisFile 2>&1 1>/dev/null");
                \peach\Shell::rm('-rf', $GenesisFile);
            }
            
            $ExecuteFile =  "{$PeachRoot}/nodeos.sh";
            $ExecuteSh = include_once(__DIR__ . '/prefabs/nodeos.sh.php');

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
            if ($this->Host != '127.0.0.1') {
                return false;
            }
            
            $PeachRoot = \peach\Pathes::var('nodeos', $this->Port);
            $NodeosPid = "{$PeachRoot}/nodeos.pid";
            
            $Pid = trim(file_get_contents($NodeosPid));
            posix_kill(intval($Pid), SIGTERM);
            
            usleep(1000);
            return !$this->isAlive();
        }
        
        return false;
    }
}
