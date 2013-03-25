#!/bin/sh
# exporte l'archive du projet avec son nom et son tag en cours
# usage: archive.sh [tag_name]
#   tag_name : Optionel, nom du tag à utilisé pour referencer le dernier commit 

last_tag=""
ref="HEAD"

#
# 1. Archive pour un tag specifique ?
#
if [ $# -gt 0 ]; then 
    #tag specifique
    last_tag=$1
    #obtient le commit du tag specifie
    ref=$(git show-ref $1 | cut -d" " -f1)
    if [ -z "$ref" ]; then echo "Error: tag not found, please specify a valid tag name"; exit; fi;
else
    # obtient le dernier tag en cours (la version)
    last_tag=$(git tag | sed -n '$p' | tr -d '\n')
    if [ -z "$ref" ]; then echo "Error: no tag found, please create tag first"; exit; fi;
fi

# obtient le nombre de commit effectue
commit_count="$(git rev-list $ref --count)"

# genere le nom de fichier pour l'arcchive
output="$(basename "$(pwd)")-$last_tag-$commit_count.zip"

#archive les fichiers
wd=$(pwd)
output="$(pwd)/../archives/$output"
echo "output archive to $output ..."
git archive -o "$output" HEAD