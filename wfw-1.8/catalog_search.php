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

//requis
if(!$app->makeFiledList(
        $arg,
        array( 'search_string' ),
        cXMLDefault::FieldFormatClassName )
   ) $app->processLastError();

//optionnels
if(!$app->makeFiledList(
        $opt_arg,
        array( 'catalog_category_id', 'item_type', 'sort' ),
        cXMLDefault::FieldFormatClassName )
   ) $app->processLastError();

$bResult = false; // affiche les resultats

// vérifie la validitée des champs
$p = array();
if(cInputFields::checkArray($arg, $opt_arg, $_REQUEST, $p))
{
    if (!CatalogModule::searchItems($items, NULL, $p->catalog_category_id, $p->search_string, $p->item_type, $p->sort, 0, 50))
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
$doc = CatalogModule::toXML(NULL,$items);
$template->push_xml_file('catalog.php', $doc);

//initialise la classe template 
if (!$template->Initialise( $template_file, NULL, $select, NULL, array_merge($att, $app->getAttributes()) ))
    return false;

//sortie
echo $template->Make();
?>