<?php
namespace wfw\daemons\kvstore\server\containers\data\storages;

use wfw\daemons\kvstore\server\containers\data\errors\InvalidKeySupplied;
use wfw\daemons\kvstore\server\containers\data\KVSStorageModeManager;
use wfw\engine\lib\data\string\compressor\GZCompressor;
use wfw\engine\lib\data\string\serializer\LightSerializer;
use wfw\engine\lib\data\string\serializer\PHPSerializer;
use wfw\engine\lib\data\string\serializer\ISerializer;


/**
 *  Stockage des clés sur le disque.
 */
final class OnDiskOnly implements KVSStorageModeManager {
	/** @var string $_basePath */
	private $_basePath;
	/** @var ISerializer $_serializer */
	private $_serializer;

	/**
	 * OnDiskOnlyStorage constructor.
	 *
	 * @param string                   $basePath   Cemin d'accé au répertoire dans lequel les données sont stockées.
	 * @param null|ISerializer $serializer (optionnel defaut : LightSerializer(GZCompressor)) Objet utilisé pour la sérialisation/déserialisation
	 */
	public function __construct(string $basePath,?ISerializer $serializer = null) {
		if(is_dir($basePath)){
			$this->_basePath = $basePath;
		}else{
			throw new \InvalidArgumentException("$basePath is not a valide directory !");
		}
		$this->_serializer = $serializer ?? new LightSerializer(
			new GZCompressor(),
			new PHPSerializer()
			);
	}

	/**
	 *  Obtient la valeur associées à une clé
	 *
	 * @param string $key Clé dont on souhaite obtenir les données
	 *
	 * @return mixed
	 */
	public function get(string $key) {
		try{
			return file_get_contents($this->_basePath."/$key/data.kvs");
		}catch(\Exception $e){
			return null;
		}
	}

	/**
	 *  Enregistre une valeur par une clé
	 *
	 * @param string      $key  Clé de stockage
	 * @param mixed       $data Données associées
	 */
	public function set(string $key, $data) {
		if($this->isValidStorageKey($key)){
			$dirPath = $this->_basePath."/".$key;
			try{
				mkdir($dirPath,0777,true);
			}catch(\Exception $e){}
			file_put_contents($dirPath."/data.kvs",$data);
		}else{
			throw new InvalidKeySupplied("$key is not a valide StorageKey !");
		}
	}

	/**
	 *  Supprime une clé et les données associées
	 *
	 * @param string $key Clé à supprimer
	 */
	public function remove(string $key) {
		try{
			unlink($this->_basePath."/$key/data.kvs");
		}catch(\Exception $e){}
	}

	/**
	 * @param string $key
	 *
	 * @return bool True si la clé existe, false sinon
	 */
	public function exists(string $key): bool {
		return $this->isValidStorageKey($key) && file_exists($this->_basePath."/$key/data.kvs");
	}

	/**
	 *  Teste la validité d'un clé. InvalidKeySuppliedException est lancée si la clé n'est pas valide.
	 *
	 * @param string $key Clé à tester
	 *
	 * @return bool
	 */
	private function isValidStorageKey(string $key):bool{
		try{
			new StorageKey($key);
			return true;
		}catch(\Exception $e){
			return false;
		}
	}
}