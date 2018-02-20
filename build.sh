#!/usr/bin/env bash

rm -r ./build
mkdir ./build
cp -r ./src ./build/mediathek

cd ./build
tar -czf mediathek.host ./mediathek

cd ..
rm -r ./build/mediathek
