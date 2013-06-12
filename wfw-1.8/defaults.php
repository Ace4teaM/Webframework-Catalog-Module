<?php
/*
    ---------------------------------------------------------------------------------------------------------------------------------------
    (C)2013 Thomas AUGUEY <contact@aceteam.org>
    ---------------------------------------------------------------------------------------------------------------------------------------
    This file is part of WebFrameWork.

    WebFrameWork is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    WebFrameWork is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with WebFrameWork.  If not, see <http://www.gnu.org/licenses/>.
    ---------------------------------------------------------------------------------------------------------------------------------------
*/

/*
 * Point d'entree des controleurs principaux
 * Rôle : Visiteur
 * UC   : user_activate_account
 */

require_once("inc/globals.php");
global $app;

/**
 * @page defaults Fusionne les fichiers defaults de tous les modules puis affiche le contenu
 * 
 */
// Initialise le document de sortie
$out = new XMLDocument("1.0", "utf-8");
if(!$out->load("default.xml"))
    exit;

//
// fusionne les dependances (autres documents 'default.xml')
//
foreach($app->getCfgSection("defaults") as $key=>$filename){
    // Initialise le document de sortie
    $in = new XMLDocument("1.0", "utf-8");
    if(!$in->load($filename))
        exit;

    //
    // pages
    //
    $out_index = $out->one(">index");
    $in_index  = $in->one(">index");
    // si il n'existe pas, clone
    if($out_index == null && $in_index != null){
        $out->documentElement->appendChild( $out->importNode($in_index,TRUE) );
    }
    else if($out_index != null && $in_index != null){
        foreach($in->all(">page[role]",$in_index) as $key=>$node){
            $id = $node->getAttribute("id");

            // si il n'existe pas, clone
            $out_node  = $out->one(">page[id=$id]",$out_index);
            if($out_node == null){
                $out_index->appendChild( $out->importNode($node,TRUE) );
                continue;
            }
        }
    }

    //
    // results
    //
    foreach($in->all(">results") as $key=>$node){
        $lang = $node->getAttribute("lang");

        // si il n'existe pas, clone
        $out_node  = $out->one(">results[lang=$lang]");
        if($out_node == null){
            $out->documentElement->appendChild( $out->importNode($node,TRUE) );
            continue;
        }

        //fusionne le restant
        XMLDocument::mergeNodesByTagName($in, $out, $node, $out_node);
    }
}

//
// definit les chemins d'accès
//
if($app->getHostName($hostname))
{
    //host
    $hostEl = $out->one(">host[id=$hostname]");
    if(!$hostEl){
        $hostEl = $out->createElement("host");
        $hostEl->setAttribute('id', $hostname);
        $out->documentElement->appendChild($hostEl);
    }

    //domaine
    $domainEl = $out->one(">domain");
    if(!$domainEl){
        $domainEl = $out->createTextElement("domain",$_SERVER["HTTP_HOST"]);
        $hostEl->appendChild($domainEl);
    }
    else
        $domainEl->nodeValue = $_SERVER["HTTP_HOST"];

    //base_path
    $base_path = $out->one(">base_path");
    if(!$base_path){
        $base_path = $out->createTextElement("base_path",substr(dirname($_SERVER["REQUEST_URI"]),1));
        $hostEl->appendChild($base_path);
    }
    else
        $base_path->nodeValue = substr(dirname($_SERVER["REQUEST_URI"]),1);

    //path
    $path = $out->one(">path");
    if(!$path){
        $path = $out->createTextElement("path", substr(dirname($_SERVER["REQUEST_URI"]),1));
        $hostEl->appendChild($path);
    }
    else
        $path->nodeValue = substr(dirname($_SERVER["REQUEST_URI"]),1);
}

//termine ici, le controleur ne doit pas s'appeler lui meme
header("content-type: text/xml");
echo '<?xml version="1.0" encoding="UTF-8" ?>' . $out->saveXML($out->documentElement);
?>