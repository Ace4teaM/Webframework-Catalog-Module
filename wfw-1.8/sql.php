/*
    Generated SQL File
*/

<?php
require_once("inc/globals.php");
global $app;

header("content-type: text/plain; charset=utf-8");

//OK pour toutes les fichiers
$sql_section = array("sql_tables","sql_func","sql_init","sql_populate");
foreach($sql_section as $key=>$section){
    $path_section = $app->getCfgSection($section);
    if(isset($path_section)){
        foreach($path_section as $name=>$path){
            $sql_file = realpath($path);

            if(file_exists($sql_file)){
                echo("/* ********************************************************************************************* */\n");
                echo("/* INCLUDE FROM $sql_file */\n");
                echo("/* ********************************************************************************************* */\n");
                echo(file_get_contents($sql_file)."\n\n");
            }
            else{
                echo("/* ********************************************************************************************* */\n");
                echo("/* NOT FOUND $path */\n");
                echo("/* ********************************************************************************************* */\n");
            }
        }
    }
}

?>