<?php

ob_start();
?>#!/bin/bash

nodeos --genesis-json "<?=$Genesis?>" \
--signature-provider <?=$Key->getPubKey()?>=KEY:<?=$Key->getPvtKey()?> \
--plugin eosio::producer_plugin --plugin eosio::producer_api_plugin \
--plugin eosio::chain_plugin --plugin eosio::chain_api_plugin \
--plugin eosio::http_plugin --plugin eosio::history_api_plugin \
--plugin eosio::history_plugin --filter-on \* \
--data-dir "<?=$PeachRoot?>/data" \
--blocks-dir "<?=$PeachRoot?>/blocks" \
--config-dir "<?=$PeachRoot?>/config" \
--producer-name <?=$Name?> \
--http-server-address <?=$HttpEp?> \
--p2p-listen-endpoint <?=$PeerEp?> \
--access-control-allow-origin=* \
--contracts-console \
--http-validate-host=false \
--verbose-http-errors \
--enable-stale-production \
<?php
foreach ($Peers as $Peer) {
    echo "--p2p-peer-address=" . $Peer->getPeerAddress() . " \\". PHP_EOL;
}
?> >> "<?=$PeachRoot?>/nodeos.log" 2>&1 & \
echo $! > "<?=$PeachRoot?>/nodeos.pid"

sleep 5

PID=`cat "<?=$PeachRoot?>/nodeos.pid"`
kill $PID

while true; do
    [ ! -d "/proc/$PID/fd" ] && break
    sleep 1
done
<?php
$Contents = ob_get_contents();
ob_end_clean();

return $Contents;