<?php 
namespace wfw\engine\lib\HTML\resources;

use Exception;

/**
 *  Gestionnaire abstrait d'inclusions de fichiers
 */
abstract class FileIncluder implements IFileIncluder {
    /**
     *  Liste des url enregistrées
     * @var string[] $_registered
     */
	protected $_registered=array();
    /**
     *  Flag d'emission d'exceptions
     * @var int $_currentFlag
     */
	protected $_currentFlag;

    /**
     *  Constructeur
     * @param int $exceptionFlag Flag de levée d'exceptions
     */

	public function __construct(int $exceptionFlag=self::EMIT_EXCEPTION_OFF)
    {
        $this->_currentFlag = $exceptionFlag;
    }

    /**
     *   Enregister un nouveau fichier à inclure (s'il n'est pas présent)
     *
     * @param  string $filePath url du fichier à inclure
     *
     * @throws Exception
     */
	public function register(string $filePath):void{
		if(!$this->isRegistered($filePath)){
			$this->_registered[]=$filePath;
		}else{
			if($this->_currentFlag==static::EMIT_EXCEPTION_ON){
				throw new Exception(
				    "Trying to register a file that have been already registered : $filePath"
                );
			}
		}
	}

    /**
     *   Supprime une url de la liste des fichiers à inclure
     *
     * @param  string $filePath URL du fichier
     *
     * @throws Exception
     */
	public function unregister(string $filePath):void{
		if($this->isRegistered($filePath)){
			array_splice($this->_registered[],array_search($filePath,$this->_registered),1);
		}else{
			if($this->_currentFlag==static::EMIT_EXCEPTION_ON){
				throw new Exception(
				    "Tying to uneregister a file that have not been registered yet : $filePath"
                );
			}
		}
	}
	/**
	 *   Permet de savoir si un URL est enregistrée
	 * @param  string    $filePath URL à tester
	 * @return boolean             True si elle est enregistrée, false sinon
	 */
	public function isRegistered(string $filePath):bool{
		return array_search($filePath,$this->_registered)===false?false:true;
	}

	/**
	 *   Retourne le code HTML des inclusions
     *
     * @param string $add_to_url (optionnel) Permet d'ajouter une partie à la fin de l'url du
     *                           fichier à inclure
	 * @return string
	 */
	public abstract function write(string $add_to_url=""):string;
}
 