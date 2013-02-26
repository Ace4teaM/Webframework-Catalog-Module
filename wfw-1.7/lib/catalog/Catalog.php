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
     * @brief Crée un catalogue XML à partir de références
     * @param $items Tableaux associatifs des items
     * @return Instance du document XML
     */
    public function catalogToXML($items) {

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
        // ajoute les définitions
        $setEl = $doc->createElement('set');
        if ($this->getDefaultFile($def)) {
            $setEl->appendChild($doc->createTextElement('title', $def->getResultText('fields', 'title')));
            $setEl->appendChild($doc->createTextElement('item_desc', $def->getResultText('fields', 'item_desc')));
        }
    
        $rootEl->appendChild($setEl);

        //------------------------------------------------------
        // ajoute les items
        if (is_array($items)) {
            foreach ($items as $key => $catalogItem) {
                $itemEl = $doc->createElement('item');
                $itemEl->setAttribute('guid', $catalogItem->catalogItemId);
                //set's
                $setEl = $doc->createElement('set');
                $setEl->appendChild($doc->createTextElement('title', $catalogItem->title));
                $setEl->appendChild($doc->createTextElement('item_desc', $catalogItem->itemDesc));
                $setEl->appendChild($doc->createTextElement('user', $catalogItem->itemDesc));
                if(EtapeRegionale::getAverageScore($catalogItem,$score,true))
                    $setEl->appendChild($doc->createTextElement('score', $score));
                if(EtapeRegionale::getOpinionCnt($catalogItem,$cnt))
                    $setEl->appendChild($doc->createTextElement('opinion', $cnt));
                if(UserAccountMgr::getByRelation($user, $catalogItem))
                    $setEl->appendChild($doc->createTextElement('user_id', $user->userAccountId));
                $itemEl->appendChild($setEl);
                //ok
                $rootEl->appendChild($itemEl);
            }
        }

        //ok
        $doc->appendChild($rootEl);

        return $doc;
    }

    public static function searchItems(&$list, $category=NULL, $text=NULL, $type=NULL, $sort=NULL, $offset=0, $limit=100)
    {
        $list = array();
        
        //obtient la bdd
        global $app;
        if(!$app->getDB($db))
            return false;
        
        $result = $db->call($app->getCfgValue("database","schema"), "catalog_find_items", array($text,$category,$type));
        if($result === false)
            return false;
        
        //offset
//        $db->rowSeek($offset);

        //extrait les données
        while($result = $db->fetchRow(NULL) && $limit--){
            if(CatalogItemMgr::getById($item,$result["catalog_item_id"]))
                array_push($list, $item);
        }

        return RESULT_OK();
    }
    
    /**
     * @brief Recherche des items
     * @param $list Tableau des instances de classes trouvés (CatalogItem)
     * @param $region Instance de la classe Region, région d'origine de  l'item
     * @param $text Texte de la recherche
     * @return Réssultat de la fonction
     * @retval true La recherche à réussi, l'argument $list est initialisé
     * @retval false Impossible d'obtenir la liste, voir Result::getLast pour plus d'informations
     */
    public static function searchItems2(&$list, $category=NULL, $text=NULL, $type=NULL, $sort=NULL, $offset=0, $limit=100)
    {
        $list = array();
        
        //obtient la bdd
        global $app;
        if(!$app->getDB($db))
            return false;
        
        //obtient les items
        $query = <<<EOT
        select catalog_item_id from catalog_item i
          inner join catalog_category c on c.catalog_category_id = i.catalog_category_id
EOT;

        //ajoute les conditions a la requete
        $cond="";
        if($text){
            $text = strtolower($text);
            $cond .= " and (lower(i.item_title) like '%$text%' or lower(i.item_desc) like '%$text%')";
        }
        if($category){
            $category = strtolower($category);
            $cond .= " and (lower(i.catalog_category_id) = '$category')";
        }
        if($type && is_string($type)){
            $type = strtolower($type);
            $cond .= " and (lower(c.item_type) = '$type')";
        }
        if(!empty($cond))
            $query.= " where 1=1 $cond";

        if($sort && is_string($sort)){
            $sort = strtolower($sort);
            $query .= " order by i.$sort";
        }

        //execute
        if(!$db->execute($query, $result))
            return false;

        //extrait les données
        while($offset<$limit && pg_result_seek($result,$offset) && $data = pg_fetch_assoc($result)){
            if(!CatalogItemMgr::getById($item,$data["catalog_item_id"]))
                return false;
            array_push($list, $item);
            $offset++;
        }

        return RESULT_OK();
    }
}

?>
