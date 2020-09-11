#!/bin/bash

BIN_PATH=`pwd`/bin
export PATH="$BIN_PATH:$PATH"
set alias cleos='cleos --wallet-url http://localhost:8888'

/bin/bash
