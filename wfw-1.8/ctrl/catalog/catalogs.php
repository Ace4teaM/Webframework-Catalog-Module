<?php

/*
  ---------------------------------------------------------------------------------------------------------------------------------------
  (C)2013 Thomas AUGUEY <contact@aceteam.org>
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
 * Liste les catalogues
 * Rôle : Visiteur
 * UC   : items
 */

class catalog_module_catalogs_ctrl extends cApplicationCtrl {

    public $fields = null;
    public $op_fields = array('catalog_type','row_offset','row_count');
    //ressources
    private $dataDoc = null; // XMLDocument

    /*
     * Constructeur
     */
    function __construct() {
        //------------------------------------------------------
        // exporte les données au format XML (catalog)
        $this->dataDoc = new XMLDocument();
        $this->dataDoc->appendChild($this->dataDoc->createElement('data'));
    }

    /*
     * Point d'entrée
     */
    function main(iApplication $app, $app_path, $p)
    {
        //recherche les catalogues
        if(!CatalogModule::searchCatalogs($list,$p->catalog_type,NULL,$p->row_offset,$p->row_count))
            return false;
        
        foreach ($list as $i=>$item){
            //ajoute les champs étendus ?
            $fields = array();
            if(!CatalogModule::getCatalogFields($item->getId(),$fields))
                return false;
            $entry = $this->dataDoc->createElement("catalog_entry");
            $this->dataDoc->appendAssocArray($entry,$fields);
            $this->dataDoc->documentElement->appendChild($entry);
        }
        
        return RESULT_OK();
    }

    /*
     * Sortie
     */
    function output(iApplication $app, $format, $att, $result) {
        if (!$result->isOK())
            return parent::output($app, $format, $att, $result);

        switch ($format) {
            case "text/xml":
                $doc = $this->dataDoc;
                //$doc->appendAssocArray($doc->documentElement,$att);
                return '<?xml version="1.0" encoding="UTF-8" ?>' . $doc->saveXML($doc->documentElement);
        }

        return parent::output($app, $format, $att, $result);
    }

};
?>