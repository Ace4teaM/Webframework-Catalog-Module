<?php
/*
    ---------------------------------------------------------------------------------------------------------------------------------------
    (C)2012-2013 Thomas AUGUEY <contact@aceteam.org>
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
  Recherche des items
  
  Role   : Tous
  UC     : Catalogs
  Module : catalog
 
  Champs:
    search_string : Texte de la recherche
    
  Champs complémentaires:
    catalog_category_id : Identifiant du catalogue
    item_type           : Type d'item à rechercher 
    sort                : Nom du champ utilisé pour le tri
 */
class catalog_module_search_ctrl extends cApplicationCtrl{
    public $fields    = array( 'search_string' );
    public $op_fields = array( 'catalog_category_id', 'item_type', 'sort' );

    function main(iApplication $app, $app_path, $p) {

        if (!CatalogModule::searchItems($items, NULL, $p->catalog_category_id, $p->search_string, $p->item_type, $p->sort, 0, 50))
            return false;

        $att = array();
        //$att = array_merge($att, $_REQUEST);

        //ajoute le catalogue (XML version)
        $doc = CatalogModule::toXML(NULL,$items);

        /* Génére la sortie */
        $format = "html";
        if(isset($_REQUEST["output"]))
            $format = $_REQUEST["output"];

        switch($format){
            case "xarg":
                break;
            case "xml":
                break;
            case "html":
                if(empty($items)){
                    //affiche le formulaire
                    echo $app->makeFormView($att,$this->fields,$this->op_fields,$p);
                    exit;
                }

                //fabrique le template
                $template_file = $app->getCfgValue("application", "main_template");

                $template = new cXMLTemplate();

                //charge le contenu en selection
                $select = new XMLDocument("1.0", "utf-8");
                $select->load("view/catalog/pages/catalog.html");

                //ajoute le fichier de configuration
                if($app->getDefaultFile($default))
                    $template->push_xml_file('default.xml',$default->doc);

                $template->push_xml_file('catalog.php', $doc);
                //initialise la classe template 
                if (!$template->Initialise( $template_file, NULL, $select, NULL, array_merge($att, $app->getAttributes()) ))
                    return false;

                //sortie
                echo $template->Make();
                exit;
            default:
                break;
        }
        
        return RESULT_OK();
    }
};
?>