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

/**
 * Gestionnaire d'utilisateur
 * Librairie PHP5
 */


require_once("php/class/bases/iModule.php");
require_once("php/xml_default.php");

class CatalogModule implements iModule
{
    /**
     * @brief Initialise le module
     * @param $local_path Chemin d'accès local vers ce dossier
     */
    public static function load($local_path){
        global $app;
        
        //initialise la configuration
        $modParam = parse_ini_file("$local_path/config.ini", true);
        $app->config = array_merge_recursive($modParam,$app->config);
    }
    
    public static function libPath(){
        global $app;
        return $app->getLibPath("catalog_module");
    }
        
    public static function makeView($name,$attributes,$template_file){ 
        return RESULT_OK();
    }

    /**
     * @brief Crée un catalogue au format XML
     * @param $items Tableaux des instances d'items (CatalogItem)
     * @return Document XML
     * @retval XMLDocument Instance du document XML
     */
    public static function toXML($items) {
        global $app;
        $items_keys = array();
        
        //------------------------------------------------------
        // exporte les données au format XML (catalog)
        $doc = new XMLDocument();

        //GUID catalog
        $rootEl = $doc->createElement('data');

        //GUID catalog
        $guidEl = $doc->createElement('guid');
        $guidEl->appendChild($doc->createTextNode('temporary'));
        $rootEl->appendChild($guidEl);
        
        //infos
        $rootEl->appendChild($doc->createTextElement('items_count', count($items)));

        //------------------------------------------------------
        // ajoute les items
        if (is_array($items)) {
            foreach ($items as $key => $catalogItem) {
                $itemEl = $doc->createElement('item');
                $itemEl->setAttribute('guid', $catalogItem->catalogItemId);
                //set's
                $setEl = $doc->createElement('set');
                if(CatalogModule::getItemsFields($catalogItem, $fields) && is_array($fields)){
                        $doc->appendAssocArray($setEl,$fields);
                        $items_keys = array_merge($items_keys,$fields);
                }
                //$setEl->appendChild($doc->createTextElement('item_title', $catalogItem->itemTitle));
                //$setEl->appendChild($doc->createTextElement('item_desc', $catalogItem->itemDesc));
                $itemEl->appendChild($setEl);
                //ok
                $rootEl->appendChild($itemEl);
            }
        }
        
        //------------------------------------------------------
        // ajoute les traductions de champs
        $setEl = $doc->createElement('set');
        if ($app->getDefaultFile($def) && is_array($items_keys)) {
            foreach($items_keys as $key=>&$value)
                $value = $def->getResultText('fields', $key);
            $doc->appendAssocArray($setEl,$items_keys);
        }
    
        $rootEl->appendChild($setEl);

        //ok
        $doc->appendChild($rootEl);

        return $doc;
    }

    /**
     * @brief Recherche des items
     * @param $list Tableau des instances d'items trouvés (CatalogItem)
     * @param $category Catégorie désiré. Si NULL, toutes les catégories sont admises
     * @param $text Texte à rechercher dans le titre ou la description
     * @param $type Type d'item admis. Si NULL, tous les types sont admis
     * @param $sort Colonne à trier. Si NULL, aucun tri
     * @param $offset Offset de départ
     * @param $limit Limite de recherche. Si -1, aucune
     * @return Résultat de procédure
     * @retval true La recherche à réussi, l'argument $list est initialisé
     * @retval false Impossible d'obtenir la liste, voir cResult::getLast pour plus d'informations
     * @remarks Les accents ne sont pas prit en compte dans la recherche.
     */
    public static function searchItems(&$list, $category=NULL, $text=NULL, $type=NULL, $sort=NULL, $offset=0, $limit=-1, &$count=NULL)
    {
        $list = array();
        
        //obtient la bdd
        global $app;
        if(!$app->getDB($db))
            return false;
        
        if(!$db->call($app->getCfgValue("database","schema"), "catalog_search_items", array($text,$category,$type), $result))
            return false;

        if($count !== NULL)
            $count = $result->rowCount();
        
        //offset
        if($offset && !$result->seek($offset,iDatabaseQuery::Origin))
            return false;

        //extrait les données
        while(is_array($row = $result->fetchRow()) && ($limit==-1 || $limit-->0)){
            if(CatalogItemMgr::getById($item,$row["catalog_item_id"]))
                array_push($list, $item);
        }

        return RESULT_OK();
    }
    
    /**
     * @brief Retourne tous les champs d'un item
     * @param $item Instance ou identifiant de l'item
     * @param $fields Tableau associatif recevant les champs
     * @return Résultat de procédure
     * @retval true La recherche à réussi, l'argument $list est initialisé
     * @retval false Impossible d'obtenir la liste, voir cResult::getLast pour plus d'informations
     * @remarks getItemsFields retourne tous les champs d'un item y compris les champs des tables étendus
     */
    public static function getItemsFields($item, &$fields)
    {
        $list = array();
        
        //obtient la bdd
        global $app;
        if(!$app->getDB($db))
            return false;
        
        //identifiant de l'item
        $item_id = $item instanceof CatalogItem ? $item->catalogItemId : $item;

        //prepare la requete
        $query = "select distinct * from catalog_item i ";
        
        //obtient le nom des tables liées à l'item
        if($db->execute("select * from catalog_items_types($item_id) as item_type", $result))
        {
            // join les tables au resultat
            $cnt=0;
            while($row = $result->fetchRow()){
                $table_name = $row["item_type"];
                $cnt++;
                $query .= " inner join $table_name j$cnt on j$cnt.catalog_item_id = i.catalog_item_id";
            }
        }
        
        //termine la requete
        $query .= " where i.catalog_item_id = $item_id;";

        //execute la requete
        if(!$db->execute($query, $result))
            return false;
        
        $fields = $result->fetchRow();
 //     print_r($fields);
        return RESULT_OK();
    }
}

?>
