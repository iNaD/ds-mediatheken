#!/usr/bin/env bash

rm -r ./build
mkdir ./build

cd ./build

tar -C ../src -cvf mediathek .
gzip --suffix=.host mediathek

cd ..
