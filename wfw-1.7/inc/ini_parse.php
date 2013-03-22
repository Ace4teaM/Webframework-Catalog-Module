<?php

/**
 * @brief Parser avancée de fichier INI
 
 * @param string $filename Le nom du fichier de configuration à analyser
 * @return array La configuration est retournée sous la forme d'un tableau associatif
 * @retval FALSE Une erreur est survenue
 * 
 * @remarks La constante ROOT_PATH doit être définit
 * 
 * @code{.ini}
 * ;; Exemple de fichier ini étendu ;;
 * 
 * ; définition d'une constante réutilisable
 * @const path = "./my_app/www"
 * 
 * ; utilisation de la constante 'path'
 * [my_section]
 * images_path    = "${path}/gfx"   ; = "./my_app/www/gfx"
 * documents_path = "${path}/doc"   ; = "./my_app/www/doc"
 * 
 * ; inclusion d'un autre fichier ini
 * @include "config/other.ini"
 */

function parse_ini_file_ex($filename){
    
    // obtient le contenu du fichier
    $content = file_get_contents($filename);
    if($content === FALSE)
        return FALSE;
    
    //
    // parse les actions
    //
    $const=array();
    do{
        $continue=0;
        
        //variables
        if(preg_match_all('/(?:^|[\n\r\s]+)@const\s+(\w+)\s*=\s*\"([^\"]*)\"/', $content, $const_matches))
        {
            foreach($const_matches[1] as $key=>&$value)
                $const['${'.$value.'}']=$const_matches[2][$key];
//            print_r($const);
            //supprime les lignes trouvées
            $content = str_replace($const_matches[0], "", $content);
        }
        //remplace les constantes
        $content = str_replace(array_keys($const), array_values($const), $content);

        //parse les options speciales
        $content = preg_replace_callback('/(?:^|[\n\r\s]+)@include\s*\"([^\"]*)\"/', function($matches) use(&$continue){
            if($content = @file_get_contents(ROOT_PATH."/".$matches[1])){
                $continue=1;//scan a nouveau le contenu inclue
                return "\n".$content."\n";
            }
            return "\n; Can't include file ".$matches[1]."\n";
        }, $content);

    }while($continue);
    
/*    header("content-type: text/plain");
    echo($content);
    exit;*/
    
    //
    // parse les sections
    //
    $sections=array();
    $default=array();
    $cur_sections=&$default;//section par defaut (perdu)
    $lines = preg_split("/(\r\n|\n|\r)/", $content);
    foreach($lines as $key=>$text){
        $text = trim($text);
        if(!strlen($text) || substr($text,0,1)==';')
            continue;
        //section ?
        if(preg_match('/\[(\w+)\]/', $text, $matches)){
            $name = strtoupper($matches[1]);
            //print_r($matches);
            if(!isset($sections[$name]))
                $sections[$name]=array();
            $cur_sections = &$sections[$name];
            continue;
        }
        //item ?
        if(preg_match('/\s*(\w+)\s*\=\s*\"?([^\"\n\r\;]*)/', $text, $matches)){
            $cur_sections[strtoupper($matches[1])]=$matches[2];
            continue;
        }
    }
    
/*    print_r($sections);*/
    
    return $sections;
}

?>