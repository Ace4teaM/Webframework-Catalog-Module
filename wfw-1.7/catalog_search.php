<?php

require_once("inc/globals.php");
global $app;

$att = array();

//fabrique le template
$template_file = $app->getCfgValue("application", "main_template");

$template = new cXMLTemplate();

//charge le contenu en selection
$select = new XMLDocument("1.0", "utf-8");
$select->load("view/catalog/pages/catalog.html");

//ajoute le fichier de configuration
$template->load_xml_file('default.xml', $app->getRootPath());

$arg     = array(
    "search_string" => "cInputString"
);
$opt_arg = array(
    "catalog_category_id" => "cInputIdentifier",
    "catalog_item_type" => "cInputIdentifier",
    "sort" => "cInputIdentifier",
);

$bResult = false; // affiche les resultats

// exemples JS
if(cInputFields::checkArray($arg, $opt_arg , $_REQUEST))
{
    //recherche les items
    $category = isset($_REQUEST["catalog_category_id"]) && !empty($_REQUEST["catalog_category_id"]) ? $_REQUEST["catalog_category_id"] : NULL;
    $sort = isset($_REQUEST["sort"]) && !empty($_REQUEST["sort"]) ? $_REQUEST["sort"] : NULL;
    $type = isset($_REQUEST["catalog_item_type"]) && !empty($_REQUEST["catalog_item_type"]) ? $_REQUEST["catalog_item_type"] : NULL;
    if (!CatalogModule::searchItems($items, NULL, $category, $_REQUEST["search_string"], $type, $sort, 0, 50))
        goto failed;

    $bResult = true;
}

goto success;
failed:
// Traduit le résultat
$att = array_merge($att, $app->translateResult(cResult::getLast()));

success:
$att = array_merge($att, $_REQUEST);

if(!$bResult){
    //affiche le formulaire
    echo $app->makeFormView($att,$arg,$opt_arg,$_REQUEST);
    exit;
}

//ajoute le catalogue (XML version)
$doc = CatalogModule::toXML($items);
$template->push_xml_file('catalog.php', $doc);

//initialise la classe template 
if (!$template->Initialise( $template_file, NULL, $select, NULL, array_merge($att, $app->getAttributes()) ))
    return false;

//sortie
echo $template->Make();
?>