<?php

define("THIS_PATH", dirname(__FILE__)); //chemin absolue vers ce script
define("ROOT_PATH", realpath(THIS_PATH."/../")); //racine du site

//fonction de parsing (fichier ini)
include('ini_parse.php');

//charge la configuration
$config = parse_ini_file_ex(ROOT_PATH."/cfg/config.ini");
if(!isset($config["PATH"]) || !isset($config["PATH"]["WFW"]) || !isset($config["PATH"]["WFW_LOCAL"])){
    echo("Cant't find Webframework Path");
    exit(-1);
}


//ajoute le chemin d'accès à WFW
set_include_path(get_include_path() . PATH_SEPARATOR . $config["PATH"]["WFW_LOCAL"]);


//instancie l'application
require_once("Application.php");
global $app;
$app = new Application(ROOT_PATH,$config);

?>