<?php

ob_start();
?>#!/bin/bash

if [ ! -d "<?=$PeachRoot?>/wallets" ]; then
    mkdir -pv "<?=$PeachRoot?>/wallets"
fi

if [ ! -d "<?=$PeachRoot?>/data" ]; then
    mkdir -pv "<?=$PeachRoot?>/data"
fi

if [ ! -d "<?=$PeachRoot?>/config" ]; then
    mkdir -pv "<?=$PeachRoot?>/config"
fi

keosd \
--plugin eosio::http_plugin \
--plugin eosio::wallet_plugin \
--plugin eosio::wallet_api_plugin \
--http-server-address <?=$Endpoint?> \
--access-control-allow-origin=* \
--wallet-dir <?=$PeachRoot?>/wallets \
-d <?=$PeachRoot?>/data \
--config-dir <?=$PeachRoot?>/config \
>> "<?=$PeachRoot?>/keosd.log" 2>&1 & \
echo $! > "<?=$PeachRoot?>/keosd.pid"

<?php
$Contents = ob_get_contents();
ob_end_clean();

return $Contents;