<?php
namespace wfw\daemons\kvstore\server\containers\data;

use wfw\daemons\kvstore\server\containers\errors\KVSContainerFailure;
use wfw\daemons\kvstore\server\environment\IKVSContainer;
use wfw\daemons\kvstore\server\KVSModes;
use wfw\engine\lib\data\string\serializer\ISerializer;

/**
 *  Gestionnaire de données de container.
 */
class KVSContainerDataManager implements IKVSContainerDataManager {
	/** @var IKVSContainer $_container */
	private $_container;
	/** @var IKVSStorageModeInflector $_inflector */
	private $_inflector;
	/** @var IKVSRegistery $_registery */
	private $_registery;
	/** @var string $_registeryPath */
	private $_registeryPath;
	/** @var bool $_disableSaveOnRemove */
	private $_disableSaveOnRemove=false;
	/** @var ISerializer $_serializer */
	private $_serializer;

	/**
	 *  KVSContainerDataManager constructor.
	 *
	 * @param IKVSContainer            $container Container géré
	 * @param IKVSStorageModeInflector $inflector Permet d'associer les modes de stockage avec un manager de stockage
	 * @param ISerializer              $serializer Permet de serialiser et compresser le registre.
	 */
	public function __construct(
		IKVSContainer $container,
		IKVSStorageModeInflector $inflector,
		ISerializer $serializer
	) {
		$this->_serializer = $serializer;
		$this->_container = $container;
		$this->_inflector = $inflector;
		$this->_registeryPath = $container->getSavePath().'/'."key_registery.serialized";

		if(file_exists($this->_registeryPath)){
			$registery = file_get_contents($this->_registeryPath);
			try{
			   $registery = $this->_serializer->unserialize($registery);
			   if(!($registery instanceof IKVSRegistery)){
				   throw new \Exception("Registery cannot be reloaded !");
			   }
			}catch(\Exception $e) {
				$registery = null;
			}
			if(!is_null($registery)){
				$this->_registery = $registery;
			}else{
				$this->_registery = new KVSRegistery();
			}
		}else{
			$this->_registery = new KVSRegistery();
		}
	}

	/**
	 * @param string $key Clé de stockage
	 *
	 * @return mixed Données associées à la clé
	 */
	public function get(string $key) {
		$key = $this->_registery->get($key);
		if(!is_null($key)){
			if($key->expired()){
				return null;
			}else{
				return $this->_inflector->getStorageModeManager($key->getStorageMode())->get($key->getName());
			}
		}else{
			return null;
		}
	}

	/**
	 *  Crée ou modifie une clé
	 *
	 * @param string      $key  Clé de stockage
	 * @param mixed       $data Données à sauvegarder.
	 * @param float       $ttl  Durée de vie de la clé
	 * @param int         $mode Mode de stockage
	 */
	public function set(string $key, $data, float $ttl = 0, ?int $mode = null) {
		$previousKey = $this->_registery->get($key);
		if(is_null($mode)){
			$mode = $this->_container->getDefaultStorageMode();
		}
		if(!is_null($previousKey)){
			$storage = $this->_inflector->getStorageModeManager($previousKey->getStorageMode());
			$storage->remove($previousKey->getName());
			$previousKey->changeTtl($ttl);
			$previousKey->changeStorageMode($mode);
		}else{
			$this->_registery->add(new KVSRegisteryKey($key,$ttl,$mode));
		}
		$storage = $this->_inflector->getStorageModeManager($mode);
		$storage->set($key,$data);
		if($mode !== KVSModes::IN_MEMORY_ONLY){
			$this->saveRegistery();
		}
	}

	/**
	 *  Ajoute une date de péremption sur une clé
	 *
	 * @param string $key Clé à modifier
	 * @param float  $ttl Nouveau temps de vie
	 */
	public function setTtl(string $key, float $ttl) {
		$key = $this->_registery->get($key);
		if(!is_null($key)){
			$key->changeTtl($ttl);
			if($key->getStorageMode() !== KVSModes::IN_MEMORY_ONLY){
				$this->saveRegistery();
			}
		}
	}

	/**
	 *  Supprime une clé du container
	 *
	 * @param string $key Clé à supprimer
	 */
	public function remove(string $key) {
		$key = $this->_registery->get($key);
		if(!is_null($key)){
			$storage = $this->_inflector->getStorageModeManager($key->getStorageMode());
			$storage->remove($key->getName());
			$this->_registery->remove($key->getName());
			if($key->getStorageMode() !== KVSModes::IN_MEMORY_ONLY && !$this->_disableSaveOnRemove){
				$this->saveRegistery();
			}
		}
	}

	/**
	 * @param string $key Clé à tester
	 *
	 * @return bool True si la clé existe et est valide, false sinon
	 */
	public function exists(string $key): bool {
		if($this->_registery->exists($key)){
			if($this->_registery->get($key)->expired()){
				$this->remove($key);
				return false;
			}else{
				return true;
			}
		}else{
			return false;
		}
	}

	/**
	 *  Supprime toutes les clés du container
	 */
	public function purge(): void {
		$atLeastOneNotInMemory = false;
		$this->_disableSaveOnRemove = true;
		foreach ($this->_registery as $key){
			/** @var IKVSRegisteryKey $key */
			$this->remove($key->getName());
			$atLeastOneNotInMemory = $atLeastOneNotInMemory || $key->getStorageMode() !== KVSModes::IN_MEMORY_ONLY;
		}
		if($atLeastOneNotInMemory){
			$this->saveRegistery();
		}
		$this->_disableSaveOnRemove = false;
	}

	/**
	 *  Réinitialise le temps de vie d'une clé
	 *
	 * @param string   $key Clé concernée
	 * @param int|null $ttl (optionnel) Nouveau temps de vie. Si non spécifié,
	 *                      l'ancien TTL défini pour la clé sera utilisé.
	 *                      Si aucun ttl n'était définit, la fonction n'a aucun effet.
	 */
	public function touch(string $key, ?int $ttl = null): void {
		$key = $this->_registery->get($key);
		if(!is_null($key)){
			$key->changeTtl($ttl ?? 0);
			$this->saveRegistery();
		}
	}

	/**
	 *  Change le mode de stockage d'une clé.
	 *
	 * @param string $key            Clé à changer de stockage.
	 * @param int    $newStorageMode Nouveau systeme de stockage (voir KVSMode)
	 */
	public function changeStorageMode(string $key, int $newStorageMode) {
		if(KVSModes::existsValue($newStorageMode)){
			$k = $this->_registery->get($key);
			if(!is_null($k)){
				$oldMode = $k->getStorageMode();
				$previousStorage = $this->_inflector->getStorageModeManager($oldMode);
				$data = $previousStorage->get($k->getName());
				$previousStorage->remove($k->getName());
				$newStorage = $this->_inflector->getStorageModeManager($newStorageMode);
				$newStorage->set($k->getName(),$data);
				$k->changeStorageMode($newStorageMode);
				$this->saveRegistery();
			}else{
				throw new KVSContainerFailure("$key not found !");
			}
		}else{
			throw new KVSContainerFailure("$newStorageMode is not a valide storage mode !");
		}
	}

	/**
	 *  Persiste le registre de clés.
	 */
	private function saveRegistery():void{
		$atLeastOneToSave = false;
		foreach($this->_registery as $k=>$key){
			/** @var IKVSRegisteryKey $key */
			if($this->exists($k) && $key->getStorageMode() !== KVSModes::IN_MEMORY_ONLY){
				$atLeastOneToSave = true;
				break;
			}
		}
		if($atLeastOneToSave){
			file_put_contents($this->_registeryPath,$this->_serializer->serialize($this->_registery));
		}else{
			try{
				unlink($this->_registeryPath);
			}catch(\Exception $e){}
		}
	}
}