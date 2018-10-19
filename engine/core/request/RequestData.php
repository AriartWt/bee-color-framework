<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 15/02/18
 * Time: 11:01
 */

namespace wfw\engine\core\request;


use stdClass;

/**
 * Données de requête HTTP.
 */
final class RequestData implements IRequestData
{
    /** @var stdClass $_get */
    private $_get;
    /** @var stdClass $_post */
    private $_post;
    /** @var stdClass $_files */
    private $_files;

    /**
     * RequestData constructor.
     *
     * @param array $get   Données GET
     * @param array $post  Données POST
     * @param array $files Fichiers envoyés.
     */
    public function __construct(?array $get=null,?array $post=null,?array $files=null)
    {
        if(!empty($get)){
            $this->_get = new stdClass();
            foreach($get as $k=>$v){
                if(!isset($this->_get->$k)){
                    $this->_get->$k=$v;
                }
            }
        }
        if(!empty($post)){
            $this->_post = new stdClass();
            foreach($post as $k=>$v){
                if($v==="true" || $v==="false"){
                    $this->_post->$k=(($v==="true")?true:false);
                }else if($v==="undefined" || $v==="null"){
                    $this->_post->$k=null;
                }else{
                    if(is_numeric($v) || (!is_numeric($v) && !is_string($v))){
                        /*préviens l'entrée de numéro de téléphone*/
                        if(is_numeric($v) && !preg_match("/^0+[0-9,.]+/",$v)){
                            if(strlen($v)<18){
                                $this->_post->$k=floatVal($v);
                            }else{
                                $this->_post->$k=$v;
                            }
                        }else{
                            $this->_post->$k=$v;
                        }
                    }else{
                        if(strlen($v)>0 && $v[0]==="\0"){
                            $this->_post->$k="".substr($v,1);
                        }else{
                            $tmp=urldecode($v);
                            //fallback pour les nombre en notation configurée : 1 000.32=>1000.32
                            if(is_numeric($tmp)){
                                $this->_post->$k=floatVal($v);
                            }else{
                                $this->_post->$k=urldecode($v);
                            }
                        }
                    }
                }
            }
        }
        if(!empty($files)){
            $this->_files = new stdClass();
            foreach ($files as $k => $v) {
                $this->_files->$k = $v;
            }
        }
    }

    /**
     * Retourne les données de la requête en fonction de $flag. Si des clés sont dupliquées, elles
     * sont écrasées dans l'ordre : GET -> POST -> FILE où '->' = 'écrasé par'.
     *
     * @param int  $flag Données à récupérer
     * @param bool $asArray (optionnel defaut : false) Si true: retourne le résultat sous forme de
     *                      tableau, sinon stdClass
     * @return stdClass|array
     */
    public function get(int $flag = self::GET | self::POST | self::FILES, bool $asArray=false)
    {
        if($asArray){
            $res =[];
            if(((int)$flag & self::GET) === self::GET && is_object($this->_get)){
                foreach($this->_get as $k=>$v){
                    $res[$k]=$v;
                }
            }
            if(((int)$flag & self::POST) === self::POST && is_object($this->_post)){
                foreach($this->_post as $k=>$v){
                    $res[$k]=$v;
                }
            }
            if(((int)$flag & self::FILES) === self::FILES && is_object($this->_files)){
                foreach($this->_files as $k=>$v){
                    $res[$k]=$v;
                }
            }
            return $res;
        }else{
            $res=new stdClass();
            if(((int)$flag & self::GET) === self::GET && is_object($this->_get)){
                foreach($this->_get as $k=>$v){
                    $res->$k=$v;
                }
            }
            if(((int)$flag & self::POST) === self::POST && is_object($this->_post)){
                foreach($this->_post as $k=>$v){
                    $res->$k=$v;
                }
            }
            if(((int)$flag & self::FILES) === self::FILES && is_object($this->_files)){
                foreach($this->_files as $k=>$v){
                    $res->$k=$v;
                }
            }
            return $res;
        }
    }

    /**
     * Supprime des indexes dans les tableaux de paramètres
     * ATTENTION : n'autorise pas les flags combinés
     *
     * @param int    $flag       Tableau ciblé
     * @param string ...$indexes Indexes à supprimer
     */
    public function remove(int $flag, string... $indexes): void
    {
        if(count($indexes)===0)
            throw new \InvalidArgumentException("At least one index to remove must be given !");

        $objectsToUnset = [];
        switch($flag){
            case $flag & self::GET === self::GET :
                $objectsToUnset[] = $this->_get;
            case $flag & self::POST === self::POST :
                $objectsToUnset[] = $this->_post;
            case $flag & self::FILES === self::FILES :
                $objectsToUnset[] = $this->_files;
                break;
            default :
                throw new \InvalidArgumentException("Unknown flag $flag.");
        }

        foreach($objectsToUnset as $obj){
            foreach($indexes as $i){
                if(isset($obj->$i)) unset($obj->$i);
            }
        }
    }
}