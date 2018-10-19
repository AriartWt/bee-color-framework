<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 30/10/17
 * Time: 03:46
 */

namespace wfw\engine\lib\data\regexp;

/**
 *  Permet de créer des regexp personalisées avec un syntaxe plus simple
 */
class CustomRegexp
{
    /**
     * Permet detraiter une regexp spéciale de l'utilisateur afin de trovuer dans une chaîne de
     * caractère plusieurs mots indépendament de l'ordre dans lequel ils sont cités.
     *
     * @param  string $regex Regex d'entrée
     *
     * @return string        Regex de sortie
     */
    function decodeRegexp(string $regex):string{
        if(preg_match("/^\(\w+((&\w+)*|)\)$/u",$regex)){
            $str=substr($regex,1);
            $str=substr($str,0,-1);
            $str=explode('&',$str);
            $res="/";
            foreach($str as $v){
                $res.="(?=.*\b".$v."\b)";
            }
            return $res."/";
        }else{
            return $regex;
        }
    }
}