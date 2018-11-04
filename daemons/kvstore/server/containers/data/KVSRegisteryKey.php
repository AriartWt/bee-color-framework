<?php
namespace wfw\daemons\kvstore\server\containers\data;

use wfw\daemons\kvstore\server\KVSModes;

/**
 *  Clé de registre KVS
 */
final class KVSRegisteryKey implements IKVSRegisteryKey {
	/** @var string $_name */
	private $_name;
	/** @var float $_ttl */
	private $_ttl;
	/** @var $_expirationDate */
	private $_expirationDate;
	/** @var int $_storageMode */
	private $_storageMode;

	/**
	 * KVSRegisteryKey constructor.
	 *
	 * @param string $name        Nom de la clé
	 * @param float  $ttl         Temps en seconde avant expiration de la clé
	 * @param int    $storageMode Mode de stockage des données
	 */
	public function __construct(
		string $name,
		float $ttl=0,
		int $storageMode=KVSModes::IN_MEMORY_PERSISTED_ON_DISK
	) {
		if(!KVSModes::existsValue($storageMode)){
			throw new \InvalidArgumentException("Unknwown KVSMode : $storageMode");
		}
		$this->_storageMode = $storageMode;
		$this->_name = $name;
		$this->_ttl = $ttl;
		$this->touch();
	}

	/**
	 * @return string Nom de la clé
	 */
	public function getName(): string {
		return $this->_name;
	}

	/**
	 * @return float Temps d'expiration définit
	 */
	public function getTtl(): float {
		return $this->_ttl;
	}

	/**
	 * @param float $ttl   Nouveau ttl
	 * @param bool  $touch (optionnel défaut : true) modifie la date d'expiration en appliquant le nouveau ttl.
	 */
	public function changeTtl(float $ttl=0,bool $touch = true){
		$this->_ttl = $ttl;
		if($ttl === 0){
			$this->_expirationDate = null;
		}else{
			if($touch){
				$this->touch();
			}
		}
	}

	/**
	 * @return bool True si la clé a expiré, false sinon
	 */
	public function expired(): bool {
		if(is_null($this->_expirationDate)){
			return false;
		}else{
			return $this->_expirationDate<microtime(true);
		}
	}

	/**
	 * @param float|null $ttl (optionnel) Temps en secondes avant expiration de la clé.
	 *                        Si non précisé rajoute le ttl de base.
	 *                        Si aucun ttl n'était défini, la fonction n'a aucun effet.
	 */
	public function touch(?float $ttl = null) {
		if(is_null($ttl) || $ttl <= 0){
			$ttl = $this->_ttl;
		}
		if($ttl > 0){
			$this->_expirationDate = microtime(true) + $ttl;
		}else{
			$this->_expirationDate = null;
		}
	}

	/**
	 * @return int Mode de stockage de la clé
	 */
	public function getStorageMode(): int {
		return $this->_storageMode;
	}

	/**
	 * @param int $mode Nouveau mode de stockage.
	 */
	public function changeStorageMode(int $mode) {
		$this->_storageMode = $mode;
	}
}