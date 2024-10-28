#!/bin/bash

directory=`basename "$PWD"`
version=`cat README.md | grep "Stable tag" | cut -d ':' -f2 | tr -d ' '`

echo $version

name="wp-kamet-atol-gateway-${version}-`date +%s`.zip"

cd ../
zip -vr $name $directory -x '*.idea/*' '*.git/*'


echo "\n"
echo "Module packed ... $name"
