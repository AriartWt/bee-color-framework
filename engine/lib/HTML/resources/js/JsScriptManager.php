<?php 
namespace wfw\engine\lib\HTML\resources\js;

use Exception;
use wfw\engine\lib\HTML\resources\FileIncluder;

/**
 *  System d'inclusion de fichiers et variables CSS
 */
final class JsScriptManager extends FileIncluder implements IJsScriptManager {
	private $_vars=array();/**<  array<string,mixed> Liste des variables à inclure */

    /**
     *   Ajoute une nouvelle variable si elle n'est pas présente
     *
     * @param  string $key   Clé (nom de variable)
     * @param  mixed  $value Valeur à écrire (doit pouvoir être encodée à l'aide de json_encode)
     *
     * @throws Exception
     */
	public function registerVar(string $key,$value):void{
		if(!$this->isRegisteredVar($key)){
            $this->_vars[$key]=$value;
		}else{
			if($this->_currentFlag==self::EMIT_EXCEPTION_ON){
				throw new Exception(
				    "Trying to register a var that have been already registered : $key=$value"
                );
			}
		}
	}
	/**
	 *   Supprime une variable de la liste des inclusions si elle est présente
	 *
	 * @param  string    $key Nom de la variable à supprimer
     * @throws Exception si la variable n'existe pas et que le currentFlag est à
     *                        self::EMIT_EXCEPTION_OFF
	 * @return void
	 */
	public function unregisterVar(string $key):void{
		if(isset($this->_vars[$key])){
			array_slice($this->_vars,array_search($key,array_keys($this->_vars)),1);
		}else{
			if($this->_currentFlag==self::EMIT_EXCEPTION_ON){
				throw new Exception("Trying to unregister a var that have not been registered yet : $key");
			}
		}
	}
	/**
	 *   Permet de savoir si une variable est déjà enreigstrée
	 * @param  string    $key Variable à tester
	 * @return boolean        True si la variable existe, false sinon
	 */
	public function isRegisteredVar(string $key):bool{
		return isset($this->_vars[$key]);
	}

	/**
	 *   Genere le code HTML d'inclusion des fichiers javascript
	 * @param  string    $add_to_url (optionnel) Chaine à ajouter à la fin de l'url des fichiers
	 * @return string
	 */
	public function write(string $add_to_url=""):string{
		$res="";
		if(count($this->_vars)>0){
			$res.='<script type="text/javascript">';
				foreach($this->_vars as $k=>$v){
					$res.="var ".$k."='";
					if(!is_object($v) && !is_array($v)){
						$res.=$v;
					}else{
						$res.=json_encode($v);
					}
					$res.="';";
				}
			$res.='</script>';
		}
		foreach($this->_registered as $v){
			$res.='<script type="text/javascript" charset="utf-8" src="'.$v.$add_to_url.'" defer></script>';
		}
		return $res;
	}
}
 