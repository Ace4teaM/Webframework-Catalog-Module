<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

define("THIS_PATH", dirname(__FILE__)); //chemin absolue vers ce script
define("ROOT_PATH", realpath(THIS_PATH."/../")); //racine du site

//recupere la constante du chemin d'accès vers wfw
//indispensable pour parser la configuration puis initialiser l'application
$ini_file_content = file_get_contents(ROOT_PATH."/cfg/config.ini");
if(!preg_match('/(?:^|[\n\r\s]+)@const\s+wfw_path\s*=\s*\"([^\"]*)/', $ini_file_content, $matches)){
    echo("Can't find Webframework Path constant in configuration file");
    exit(-1);
}
$wfw_path = trim($matches[1]);

//ajoute le chemin d'accès à WFW
set_include_path(get_include_path() . PATH_SEPARATOR . $wfw_path."/php");

//fonction de parsing (fichier ini)
include('ini_parse.php');

//charge la configuration
$config = parse_ini_string_ex($ini_file_content);
if(!isset($config["PATH"]) || !isset($config["PATH"]["WFW"]) || !isset($config["PATH"]["WFW_LOCAL"])){
    echo("Cant't find Webframework Path");
    exit(-1);
}


//instancie l'application
require_once("Application.php");
global $app;
$app = new Application(ROOT_PATH,$config);

?>