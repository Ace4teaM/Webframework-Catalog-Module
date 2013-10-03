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


require_once("class/bases/iModule.php");
require_once("xml_default.php");

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
     * @brief Crée un catalogue
     * @param $inst Pointeur recevant l'instance du catalogue (CatalogEntry)
     * @param $type Type de catalogue à initialiser (nom d'une table héritant de CATALOG_ENTRY)
     * @param $typeInst Instance de la classe correspondant au type 'Type'
     * @return Résultat de procédure
     * @retval true La recherche à réussi, l'argument $list est initialisé
     * @retval false Impossible d'obtenir la liste, voir cResult::getLast pour plus d'informations
     * @remarks getItemsFields retourne tous les champs d'un item y compris les champs des tables étendus
     */
    public static function createCatalog(&$inst, $type, $typeInst)
    {
        //obtient la bdd
        global $app;
        if(!$app->getDB($db))
            return false;
        
        if(!$db->call($app->getCfgValue("database","schema"), "make_id", array('catalog_entry','catalog_entry_id'), $result))
            return false;

        $row = $result->fetchRow();

        //return $result;
        $result = new cResult($row["err_code"], $row["err_str"], stra_to_array($row["ext_fields"]));

        $inst = new CatalogEntry();
        $inst->catalogEntryId = intval($result->getAtt("ID"));
        $inst->catalogType = $type;

        if(!CatalogEntryMgr::insert($inst))
            return false;

        $typeInst->setId($inst->catalogEntryId);
        $typeInst->catalogEntryId = $inst->catalogEntryId;
        $mgr = str_replace("_", "", $type)."Mgr";
        if(!$mgr::insert($typeInst, array("CATALOG_ENTRY_ID"=>$inst->getId())))
            return false;
        
        return RESULT_OK();
    }
    
    /**
     * @brief Crée un item
     * @param $catalog Instance ou identifiant du catalogue recevant le nouvel item (CatalogEntry)
     * @param $item Pointeur recevant l'instance du nouvel item (CatalogItem)
     * @param $title Titre de l'item
     * @param $desc Description de l'item
     * @param $category Tableau des catégories à associés
     * @param $fields Tableau associatif des champs à initialiser
     * @return Résultat de procédure
     * @retval true La recherche à réussi, l'argument $list est initialisé
     * @retval false Impossible d'obtenir la liste, voir cResult::getLast pour plus d'informations
     * @remarks 'createItem' utilise la methode 'setItemsFields' pour initialiser les champs
     */
    public static function createItem($catalog, &$inst, $title, $desc, $category, $fields)
    {
        //obtient la bdd
        global $app;
        if(!$app->getDB($db))
            return false;
        
        //identifiant de l'item
        $catalog_entry_id = $catalog instanceof CatalogEntry ? $catalog->getId() : $catalog;

        //
        if(!$db->call($app->getCfgValue("database","schema"), "make_id", array('catalog_item','catalog_item_id'), $result))
            return false;

        $row = $result->fetchRow();

        //return $result;
        $result = new cResult($row["err_code"], $row["err_str"], stra_to_array($row["ext_fields"]));

        $inst = new CatalogItem();
        $inst->catalogItemId = intval($result->getAtt("ID"));
        $inst->itemTitle = $title;
        $inst->itemDesc  = $desc;
        $inst->creationDate  = date(DATE_RFC822);

        if(!CatalogItemMgr::insert($inst,array("catalog_entry_id"=>$catalog_entry_id)))
            return false;
        
        //associe les categories
        if(is_array($category)){
            foreach($category as $key=>$type)
            {
                //obtient le nom des tables liées à l'item
                if(!$db->execute("INSERT INTO CATALOG_ASSOCIER VALUES($inst->catalogItemId, '$type')", $result))
                    return false;
            }
        }
        
        //associe les données
        if(is_array($fields)){
          if(!CatalogModule::setItemFields($inst, $fields))
            return false;
        }
        
        return RESULT_OK();
    }
    
    /**
     * @brief Initialise les champs d'un item
     * @param $item Instance ou identifiant de l'item à modifier
     * @param $fields Tableau associatif des champs à initialiser
     * @return Résultat de procédure
     * @retval true La recherche à réussi, l'argument $list est initialisé
     * @retval false Impossible d'obtenir la liste, voir cResult::getLast pour plus d'informations
     * @remarks setItemFields initialise automatiquement les diverses tables associé à l'item. Si aucune entrée existe setItemFields réalise une opération INSERT sion une opération UPDATE est effectuée.
     * @remarks Si une opération INSERT est nécessaire, veillez à renseigner tout les champs obligatoires. Dans le cas contraire la fonction échouera.
     */
    public static function setItemFields($item, $fields)
    {
        //obtient la bdd
        global $app;
        if(!$app->getDB($db))
            return false;
        
        //identifiant de l'item
        $item_id = $item instanceof CatalogItem ? $item->getId() : $item;

        //obtient les tables associées à l'item
        if(!CatalogModule::getItemsTypes($item_id, $list))
            return false;
        
        //initialise les colonnes des tables associées
        $query = "select table_name, column_name from INFORMATION_SCHEMA.COLUMNS where table_name IN('".strtolower(implode("','",$list))."');";
        if(!$db->execute($query,$result))
                return false;
        
        // liste les colonnes qui serons affectées (par table)
        $table_columns = array();
        $cnt=0;
        while($result->seek($cnt++,iDatabaseQuery::Origin))
        {
            $table_name = $result->fetchValue("table_name");
            $column_name = $result->fetchValue("column_name");

            if(!isset($fields[$column_name]))
                continue;
            
            if(!isset($table_columns[$table_name]))
                $table_columns[$table_name] = array();
            array_push($table_columns[$table_name],$column_name);
        }
        
        //initialise les tables
        foreach($table_columns as $table_name=>$columns)
        {
            //test si une entree existe
            if(!$db->execute("SELECT true FROM $table_name WHERE ".$table_name."_id=".$db->parseValue($item_id),$test_result))
                return false;
            $query = "";
            if($test_result->rowCount()){
                //met a jour l'entree
                $query = "UPDATE $table_name SET ";
                foreach($columns as $key=>$column_name)
                    $query.="$column_name=".$db->parseValue($fields[$column_name]).",";
                $query = substr($query,0,-1);
                $query .= " where ".$table_name."_id=".$db->parseValue($item_id);
            }
            else{
                //met a jour l'entree
                $query = "INSERT INTO $table_name (".$table_name."_id, catalog_item_id, ".implode(',',$columns).") VALUES( ".$db->parseValue($item_id).",  ".$db->parseValue($item_id).", ";
                foreach($columns as $key=>$column_name)
                    $query .= $db->parseValue($fields[$column_name]).",";
                $query = substr($query,0,-1);
                $query .= ")";
            }
            if(!$db->execute($query,$result))
                return false;
        }
        
        return RESULT_OK();
    }
    
    /**
     * @brief Crée un catalogue au format XML
     * @param $items Tableaux des instances d'items (CatalogItem)
     * @return Document XML
     * @retval XMLDocument Instance du document XML
     */
    public static function toXML($catalog,$items) {
        global $app;
        $items_keys = array();
        
        //------------------------------------------------------
        // exporte les données au format XML (catalog)
        $doc = new XMLDocument();
  
        //Cree l'élément racine
        $rootEl = $doc->createElement('data');

        $rootEl->appendChild($doc->createComment( "Infos" ));
          
        //GUID catalog
        $guidEl = $doc->createElement('guid');
        $guidEl->appendChild($doc->createTextNode('temporary'));
        $rootEl->appendChild($guidEl);
        
        //infos
        $rootEl->appendChild($doc->createTextElement('items_count', count($items)));

        //------------------------------------------------------
        // ajoute le catalogue
        if($catalog instanceof CatalogEntry){
            $rootEl->appendChild($doc->createComment( "Catalog entity" ));
            $catalogEl = CatalogEntryMgr::toXML($catalog, $doc);
            $rootEl->appendChild($catalogEl);

            $typeClassName = ucfirst($catalog->catalogType)."Mgr";
            if($typeClassName::get($item,"catalog_entry_id=".$catalog->catalogEntryId)){
                $itemEl = $typeClassName::toXML($item, $doc);
                $rootEl->appendChild($itemEl);
            }
        }
        
        //------------------------------------------------------
        // ajoute les items
        if (is_array($items)) {
            $rootEl->appendChild($doc->createComment( "Items list" ));
            foreach ($items as $key => $catalogItem) {
                $itemEl = $doc->createElement('item');
                $itemEl->setAttribute('guid', $catalogItem->catalogItemId);
                //types et categories
                if(CatalogModule::getItemsCategory($catalogItem, $categoryList)){
                    $cat = "";
                    $type = "";
                    foreach($categoryList as $key=>$categoryItem){
                        $cat.= $categoryItem->catalogCategoryId." ";
                        $type.= $categoryItem->itemType." ";
                    }
                    $itemEl->setAttribute('category', $cat);
                    $itemEl->setAttribute('type', $type);
                }
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
        $rootEl->appendChild($doc->createComment( "Field descriptions" ));
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
    public static function searchItems(&$list, $catalog_id=NULL, $category=NULL, $text=NULL, $type=NULL, $sort=NULL, $offset=0, $limit=-1, &$count=NULL)
    {
        $list = array();
        
        //obtient la bdd
        global $app;
        if(!$app->getDB($db))
            return false;
        
        if(!$db->call($app->getCfgValue("database","schema"), "catalog_search_items", array($text,$catalog_id,$category,$type,$sort), $result))
            return false;

        if($count !== NULL)
            $count = $result->rowCount();
        
        //offset
        if($offset && !$result->seek($offset,iDatabaseQuery::Origin))
            return false;

        //extrait les données
        while(is_array($row = $result->fetchRow()) && ($limit==-1 || $limit-- > 0)){
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
    
    /**
     * @brief Liste les tables associées à un item
     * @param $item Instance ou identifiant de l'item (CatalogItem)
     * @param $list Identifiants des catégories trouvées (string[])
     * @return Résultat de procédures
     */
    public static function getItemsTypes($item,&$list)
    {
        $list = array();
        
        //obtient la bdd
        global $app;
        if(!$app->getDB($db))
            return false;

        //identifiant de l'item
        $item_id = $item instanceof CatalogItem ? $item->getId() : $item;

        //obtient le nom des tables liées à l'item
        if($db->execute("select * from catalog_items_types($item_id) as item_type", $result))
        {
            // join les tables au resultat
            $cnt=0;
            while($result->seek($cnt,iDatabaseQuery::Origin)){
                $type = $result->fetchValue("item_type");
                array_push($list,$type);
                $cnt++;
            }
        }
        
        return $list;
    }
    
    /**
     * @brief Vérifie si un item appartient à un type donné
     * @param $item Instance ou identifiant de l'item (CatalogItem)
     * @param $type Type d'item
     * @return Résultat de procédures ou test
     * @retval false L'item n'est pas associé à ce type
     * @retval true L'item est associé à ce type
     */
    public static function hasItemType($item,$type)
    {
        $list = array();
        
        //obtient la bdd
        global $app;
        if(!$app->getDB($db))
            return false;

        //identifiant de l'item
        $item_id = $item instanceof CatalogItem ? $item->getId() : $item;

        //obtient le nom des tables liées à l'item
        if($db->execute("select count(*) as count from catalog_associer a
                            inner join catalog_category c on c.catalog_category_id = a.catalog_category_id and c.item_type = '$type'
                            where a.catalog_item_id = $item_id;
                            ", $result))
        {
            RESULT_OK();
            if($result->fetchValue("count") == "1")
                return true;
            return false;
        }
        
        return false;
    }
    
    /**
     * @brief Liste les catégories associées à un item
     * @param $item Instance ou identifiant de l'item (CatalogItem)
     * @param $list Instances des catégories trouvées (CatalogCategory[])
     * @return Résultat de procédures
     */
    public static function getItemsCategory($item,&$list)
    {
        $list = array();
        
        //obtient la bdd
        global $app;
        if(!$app->getDB($db))
            return false;

        //identifiant de l'item
        $item_id = $item instanceof CatalogItem ? $item->catalogItemId : $item;

        //prepare la requete
        $query = "select * from catalog_items_category($item_id);";

        //obtient le nom des tables liées à l'item
        if(!$db->execute($query, $result))
            return false;

        $i=0;
        while($result->seek($i,iDatabaseQuery::Origin)){
            //if($item->catalogItemId==1)
            //    echo($i);
            $cat = new CatalogCategory();
            CatalogCategoryMgr::bindResult($cat, $result);
            $i++;
            array_push($list, $cat);
        }

        return RESULT_OK();
    }
    
    /**
     * @brief Liste les catégories triées par type d'item
     * @param $type Type d'item
     * @param $list Instances des catégories trouvées (CatalogCategory[])
     * @return Résultat de procédures
     */
    public static function getCategoryByType($type,&$list)
    {
        $list = array();
        
        //obtient la bdd
        global $app;
        if(!$app->getDB($db))
            return false;

        //prepare la requete
        $query = "select distinct * from catalog_category where ";
        if(is_array($type))
            $query .= "item_type in('".implode("','", $type)."');";
        else
            $query .= "item_type = '$type';";

        //obtient le nom des tables liées à l'item
        if(!$db->execute($query, $result))
            return false;

        $i=0;
        while($result->seek($i,iDatabaseQuery::Origin)){
            $cat = new CatalogCategory();
            CatalogCategoryMgr::bindResult($cat, $result);
            $i++;
            array_push($list, $cat);
        }
        

        return RESULT_OK();
    }
    
    
    /**
     * @brief Retourne tous les champs d'un catalogue
     * @param $entry Instance ou identifiant du catalogue
     * @param $fields Tableau associatif recevant les champs
     * @return Résultat de procédure
     * @retval true La recherche à réussi, l'argument $list est initialisé
     * @retval false Impossible d'obtenir la liste, voir cResult::getLast pour plus d'informations
     * @remarks getCatalogFields retourne tous les champs d'un catalogue y compris les champs étendus
     */
    public static function getCatalogFields($entry, &$fields)
    {
        //obtient la bdd
        global $app;
        if(!$app->getDB($db))
            return false;
        
        //identifiant du catalogue
        $catalog_entry_id = $entry instanceof CatalogEntry ? $entry->getId() : $entry;

        //prepare la requete
        $query = "select * from catalog_entry i ";
        
        //obtient le nom de la table étendue
        if($db->execute("select catalog_type from catalog_entry where catalog_entry_id='$catalog_entry_id'", $result))
        {
            // join la table au resultat
            $table_name = $result->fetchValue("catalog_type");
            if($table_name !== false){
                $query .= " inner join $table_name e on e.catalog_entry_id = i.catalog_entry_id";
            }
        }
        else
            return false;
        
        //termine la requete
        $query .= " where i.catalog_entry_id = $catalog_entry_id;";

        //execute la requete
        if(!$db->execute($query, $result))
            return false;
        
        $fields = $result->fetchRow();
 //     print_r($fields);
        return RESULT_OK();
    }
    
    /**
     * @brief Recherche des catalogues
     * @param $list Tableau des instances trouvés (CatalogEntry)
     * @param $type Type de catalogue admis. Si NULL, tous les types
     * @param $sort Colonne à trier. Si NULL, aucun tri
     * @param $offset Offset de départ
     * @param $limit Limite de recherche. Si -1, aucune
     * @return Résultat de procédure
     * @retval true La recherche à réussi, l'argument $list est initialisé
     * @retval false Impossible d'obtenir la liste, voir cResult::getLast pour plus d'informations
     * @remarks Les accents ne sont pas prit en compte dans la recherche.
     */
    public static function searchCatalogs(&$list, $type=NULL, $sort=NULL, $offset=0, $limit=-1, &$count=NULL)
    {
        $list = array();
        $db = null;
        
        //obtient la bdd
        global $app;
        if(!$app->getDB($db))
            return false;
        
        if(!$db->call($app->getCfgValue("database","schema"), "catalog_search_entry", array($type,$sort), $result))
            return false;

        if($count !== NULL)
            $count = $result->rowCount();
        
        //offset
        if($offset && !$result->seek($offset,iDatabaseQuery::Origin))
            return false;

        //extrait les données
        while(is_array($row = $result->fetchRow()) && ($limit==-1 || $limit-- > 0)){
            $item = null;
            if(CatalogEntryMgr::getById($item,$row["catalog_entry_id"]))
                array_push($list, $item);
        }

        return RESULT_OK();
    }
    
}

?>
