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
class Vehicle
{
    
    /**
    * @var      int
    */
    public $vehicleId;
    
    /**
    * @var      String
    */
    public $type;
    
    /**
    * @var      int
    */
    public $nPlaces;
    
    /**
    * @var      int
    */
    public $nDoors;
    
    /**
    * @var      float
    */
    public $consumption;    

}

/*
   vehicle Class manager
   
   This class is optimized for use with the Webfrmework project (www.webframework.fr)
*/
class VehicleMgr
{
    /**
     * @brief Convert existing instance to XML element
     * @param $inst Entity instance (Vehicle)
     * @param $doc Parent document
     * @return New element node
     */
    public static function toXML(&$inst,$doc) {
        $node = $doc->createElement("Vehicle");
        
        $node->appendChild($doc->createTextElement("vehicle_id",$inst->vehicleId));
        $node->appendChild($doc->createTextElement("type",$inst->type));
        $node->appendChild($doc->createTextElement("n_places",$inst->nPlaces));
        $node->appendChild($doc->createTextElement("n_doors",$inst->nDoors));
        $node->appendChild($doc->createTextElement("consumption",$inst->consumption));       

          
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
       $query = "SELECT * from vehicle where $cond";
       if(!$db->execute($query,$result))
          return false;
       
      //extrait les instances
       $i=0;
       while($result->seek($i)){
        $inst = new Vehicle();
        VehicleMgr::bindResult($inst,$result);
        array_push($list,$inst);
        $i++;
       }
       
       return true;
    }
    
    /*
      @brief Get single entry
      @param $inst Vehicle instance pointer to initialize
      @param $cond SQL Select condition
      @param $db iDataBase derived instance
    */
    public static function bindResult(&$inst,$result){
          $inst->vehicleId = $result->fetchValue("vehicle_id");
          $inst->type = $result->fetchValue("type");
          $inst->nPlaces = $result->fetchValue("n_places");
          $inst->nDoors = $result->fetchValue("n_doors");
          $inst->consumption = $result->fetchValue("consumption");          

       return true;
    }
    
    /*
      @brief Get single entry
      @param $inst Vehicle instance pointer to initialize
      @param $cond SQL Select condition
      @param $db iDataBase derived instance
    */
    public static function get(&$inst,$cond,$db=null){
       //obtient la base de donnees courrante
       global $app;
       if(!$db && !$app->getDB($db))
         return false;
      
      //execute la requete
       $query = "SELECT * from vehicle where $cond";
       if($db->execute($query,$result)){
            $inst = new Vehicle();
          return VehicleMgr::bindResult($inst,$result);
       }
       return false;
    }
    
    /*
      @brief Get single entry by id
      @param $inst Vehicle instance pointer to initialize
      @param $id Primary unique identifier of entry to retreive
      @param $db iDataBase derived instance
    */
    public static function getById(&$inst,$id,$db=null){
       //obtient la base de donnees courrante
       global $app;
       if(!$db && !$app->getDB($db))
         return false;
      
       if(is_string($id))
           $id = "'$id'";
           
      //execute la requete
       $query = "SELECT * from vehicle where vehicle_id=$id";
       if($db->execute($query,$result)){
            $inst = new Vehicle();
          $inst->vehicleId = $result->fetchValue("vehicle_id");
          $inst->type = $result->fetchValue("type");
          $inst->nPlaces = $result->fetchValue("n_places");
          $inst->nDoors = $result->fetchValue("n_doors");
          $inst->consumption = $result->fetchValue("consumption");          

          return true;
       }
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
      @param $inst Vehicle instance pointer to initialize
      @param $obj An another entry class object instance
      @param $db iDataBase derived instance
    */
    public static function getByRelation(&$inst,$obj,$db=null){
        $objectName = get_class($obj);
        $objectTableName  = VehicleMgr::nameToCode($objectName);
        $objectIdName = lcfirst($objectName)."Id";
        
        /*print_r($objectName.", ");
        print_r($objectTableName.", ");
        print_r($objectIdName.", ");
        print_r($obj->$objectIdName);*/
        
        $select;
        if(is_string($obj->$objectIdName))
            $select = ("vehicle_id = (select vehicle_id from $objectTableName where ".$objectTableName."_id='".$obj->$objectIdName."')");
        else
            $select = ("vehicle_id = (select vehicle_id  from $objectTableName where ".$objectTableName."_id=".$obj->$objectIdName.")");

        return VehicleMgr::get($inst,$select,$db);
    }

}

?>