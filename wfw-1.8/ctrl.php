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
 * Point d'entree des controleurs principaux
 * Rôle : Visiteur
 * UC   : user_activate_account
 */

require_once("inc/globals.php");
global $app;

// Champs requis
if(!$app->makeFiledList(
        $fields,
        array( 'ctrl' ),
        cXMLDefault::FieldFormatClassName )
   ) $app->processLastError();

// Champs requis
if(!$app->makeFiledList(
        $op_fields,
        array( 'app' ),
        cXMLDefault::FieldFormatClassName )
   ) $app->processLastError();

// vérifie la validitée des champs
$p = array();
if(!cInputFields::checkArray($fields,$op_fields,$_REQUEST,$p))
    $app->processLastError();

$app->execCtrl($p->ctrl,$p->app,$app->getRole());

?>