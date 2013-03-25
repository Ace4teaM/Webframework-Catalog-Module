<?php

//inclue le model de l'application
require_once("php/class/bases/cApplication.php");


class Application extends cApplication
{
    //surcharge makeXMLView avec les paramétres du template principale
    public function makeXMLView($filename,$attributes,$template_file=NULL)
    {
        //status de la base de données
        $attributes["bdd_status"] = "Indisponible, vérifiez la configuration de l'application et l'installation de votre SGBD";
        
        if($this->getDB($db_iface)){
            $attributes["bdd_status"] = $db_iface->getServiceProviderName();
            $attributes["bdd_status"] .= " ( ".$this->getCfgValue("database", "name")." @ ".$this->getCfgValue("database", "server")." : ".$this->getCfgValue("database", "port")." )";
        }
        
        return parent::makeXMLView($filename,$attributes,$template_file);
    }
}

?>
