#!/bin/sh
#exporte l'archive du projet avec son nom et son tag en cours
#output="$(basename $(pwd))-$(git tag | sed -n 1p | tr -d '\n').zip"
#output="$(basename "$(pwd)")-$(git describe | sed -n 1p | tr -d '\n').zip"
output="$(basename "$(pwd)")-$(git tag | sed -n 1p | tr -d '\n')-$(git rev-list HEAD --count).zip"

wd=$(pwd)
#cd ..
output="$(pwd)/../archives/$output"
echo "output archive to $output ..."
#cd $wd
git archive -o "$output" HEAD