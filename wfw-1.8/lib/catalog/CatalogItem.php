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
 *  Webframework Module
 *  PHP Data-Model Implementation
*/


/**
* @author       AceTeaM
*/
class CatalogItem
{
   public function getId(){
      return $this->catalogItemId;
  }
   public function setId($id){
      return $this->catalogItemId = $id;
  }

    
    /**
    * @var      int
    */
    public $catalogItemId;
    
    /**
    * @var      String
    */
    public $itemTitle;
    
    /**
    * @var      String
    */
    public $itemDesc;
    
    /**
    * @var      Date
    */
    public $creationDate;    

}

/*
   catalog_item Class manager
   
   This class is optimized for use with the Webfrmework project (www.webframework.fr)
*/
class CatalogItemMgr
{
    /**
     * @brief Convert existing instance to XML element
     * @param $inst Entity instance (CatalogItem)
     * @param $doc Parent document
     * @return New element node
     */
    public static function toXML(&$inst,$doc) {
        $node = $doc->createElement(strtolower("CatalogItem"));
        
        $node->appendChild($doc->createTextElement("catalog_item_id",$inst->catalogItemId));
        $node->appendChild($doc->createTextElement("item_title",$inst->itemTitle));
        $node->appendChild($doc->createTextElement("item_desc",$inst->itemDesc));
        $node->appendChild($doc->createTextElement("creation_date",$inst->creationDate));       

          
        return $node;
    }
    
    
    /*
      @brief Get entry list
      @param $list Array to receive new instances
      @param $cond SQL Select condition
      @param $db iDataBase derived instance
    */
    public static function getAll(&$list,$cond,$db=null){
       $list = array();
      
       //obtient la base de donnees courrante
       global $app;
       if(!$db && !$app->getDB($db))
         return false;
      
      //execute la requete
       $query = "SELECT * from catalog_item where $cond";
       if(!$db->execute($query,$result))
          return false;
       
      //extrait les instances
       $i=0;
       while( $result->seek($i,iDatabaseQuery::Origin) ){
        $inst = new CatalogItem();
        CatalogItemMgr::bindResult($inst,$result);
        array_push($list,$inst);
        $i++;
       }
       
       return RESULT_OK();
    }
    
    /*
      @brief Get single entry
      @param $inst CatalogItem instance pointer to initialize
      @param $cond SQL Select condition
      @param $db iDataBase derived instance
    */
    public static function bindResult(&$inst,$result){
          $inst->catalogItemId = $result->fetchValue("catalog_item_id");
          $inst->itemTitle = $result->fetchValue("item_title");
          $inst->itemDesc = $result->fetchValue("item_desc");
          $inst->creationDate = $result->fetchValue("creation_date");          

       return true;
    }
    
    /*
      @brief Get single entry
      @param $inst CatalogItem instance pointer to initialize
      @param $cond SQL Select condition
      @param $db iDataBase derived instance
    */
    public static function get(&$inst,$cond,$db=null){
       //obtient la base de donnees courrante
       global $app;
       if(!$db && !$app->getDB($db))
         return false;
      
      //execute la requete
       $query = "SELECT * from catalog_item where $cond";
       if($db->execute($query,$result)){
            $inst = new CatalogItem();
             if(!$result->rowCount())
                 return RESULT(cResult::Failed,iDatabaseQuery::EmptyResult);
          return CatalogItemMgr::bindResult($inst,$result);
       }
       return false;
    }
    
    /*
      @brief Get single entry by id
      @param $inst CatalogItem instance pointer to initialize
      @param $id Primary unique identifier of entry to retreive
      @param $db iDataBase derived instance
    */
    public static function getById(&$inst,$id,$db=null){
       //obtient la base de donnees courrante
       global $app;
       if(!$db && !$app->getDB($db))
         return false;
      
      //execute la requete
       $query = "SELECT * from catalog_item where catalog_item_id=".$db->parseValue($id);
       if($db->execute($query,$result)){
            $inst = new CatalogItem();
             if(!$result->rowCount())
                 return RESULT(cResult::Failed,iDatabaseQuery::EmptyResult);
             self::bindResult($inst,$result);
          return true;
       }
       return false;
    }
    
   /*
      @brief Insert single entry with generated id
      @param $inst WriterDocument instance pointer to initialize
      @param $add_fields Array of columns names/columns values of additional fields
      @param $db iDataBase derived instance
    */
    public static function insert(&$inst,$add_fields=null,$db=null){
       //obtient la base de donnees courrante
       global $app;
       if(!$db && !$app->getDB($db))
         return false;
      
       //id initialise ?
       if(!isset($inst->catalogItemId)){
            $table_name = 'catalog_item';
            $table_id_name = $table_name.'_id';
           if(!$db->execute("select * from new_id('$table_name','$table_id_name');",$result))
              return RESULT(cResult::Failed, cApplication::EntityMissingId);
           $inst->catalogItemId = intval($result->fetchValue("new_id"));
       }
       
      //execute la requete
       $query = "INSERT INTO catalog_item (";
       $query .= " catalog item id,";
       $query .= " item title,";
       $query .= " item desc,";
       $query .= " creation_date,";
       if(is_array($add_fields))
           $query .= implode(',',array_keys($add_fields)).',';
       $query = substr($query,0,-1);//remove last ','
       $query .= ")";
       
       $query .= " VALUES(";
       $query .= $db->parseValue($inst->catalogItemId).",";
       $query .= $db->parseValue($inst->itemTitle).",";
       $query .= $db->parseValue($inst->itemDesc).",";
       $query .= $db->parseValue($inst->creationDate).",";
       if(is_array($add_fields))
           $query .= implode(',',$add_fields).',';
       $query = substr($query,0,-1);//remove last ','
       $query .= ")";
       
       if($db->execute($query,$result))
          return true;

       return false;
    }
    
   /*
      @brief Update single entry by id
      @param $inst WriterDocument instance pointer to initialize
      @param $db iDataBase derived instance
    */
    public static function update(&$inst,$db=null){
       //obtient la base de donnees courrante
       global $app;
       if(!$db && !$app->getDB($db))
         return false;
      
       //id initialise ?
       if(!isset($inst->catalogItemId))
           return RESULT(cResult::Failed, cApplication::EntityMissingId);
      
      //execute la requete
       $query = "UPDATE catalog_item SET";
       $query .= " catalog item id =".$db->parseValue($inst->catalogItemId).",";
       $query .= " item title =".$db->parseValue($inst->itemTitle).",";
       $query .= " item desc =".$db->parseValue($inst->itemDesc).",";
       $query .= " creation_date =".$db->parseValue($inst->creationDate).",";
       $query = substr($query,0,-1);//remove last ','
       $query .= " where catalog_item_id=".$db->parseValue($inst->catalogItemId);
       if($db->execute($query,$result))
          return true;

       return false;
    }
    
   /** @brief Convert name to code */
    public static function nameToCode($name){
        for($i=strlen($name)-1;$i>=0;$i--){
            $c = substr($name, $i, 1);
            if(strpos("ABCDEFGHIJKLMNOPQRSTUVWXYZ",$c) !== FALSE){
                $name = substr_replace($name,($i?"_":"").strtolower($c), $i, 1);
            }
        }
        return $name;
    }
    
    /**
      @brief Get entry by id's relation table
      @param $inst CatalogItem instance pointer to initialize
      @param $obj An another entry class object instance
      @param $db iDataBase derived instance
    */
    public static function getByRelation(&$inst,$obj,$db=null){
        $objectName = get_class($obj);
        $objectTableName  = CatalogItemMgr::nameToCode($objectName);
        $objectIdName = lcfirst($objectName)."Id";
        
        /*print_r($objectName.", ");
        print_r($objectTableName.", ");
        print_r($objectIdName.", ");
        print_r($obj->$objectIdName);*/
        
        $select;
        if(is_string($obj->$objectIdName))
            $select = ("catalog_item_id = (select catalog_item_id from $objectTableName where ".$objectTableName."_id='".$obj->$objectIdName."')");
        else
            $select = ("catalog_item_id = (select catalog_item_id  from $objectTableName where ".$objectTableName."_id=".$obj->$objectIdName.")");

        return CatalogItemMgr::get($inst,$select,$db);
    }

}

?>