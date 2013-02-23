<?php

define("THIS_PATH", dirname(__FILE__)); //chemin absolue vers ce script
define("ROOT_PATH", realpath(THIS_PATH."/../")); //racine du site

//charge la configuration
$config = parse_ini_file(ROOT_PATH."/cfg/config.ini", true);
$config = array_change_key_case($config, CASE_UPPER);
if(!isset($config["PATH"])){
    echo("Cant't find configuration path section");
    exit(-1);
}
$config["PATH"] = array_change_key_case($config["PATH"], CASE_UPPER);
if(!isset($config["PATH"]["WFW"])){
    echo("Cant't find Webframework Path");
    exit(-1);
}

//ajoute le chemin d'accès à WFW
set_include_path(get_include_path() . PATH_SEPARATOR . $config["PATH"]["WFW"]);

//instancie l'application
require_once("Application.php");
global $app;
$app = new Application(ROOT_PATH,$config);

?>