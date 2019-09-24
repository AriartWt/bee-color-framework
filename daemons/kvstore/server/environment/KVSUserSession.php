<?php
namespace wfw\daemons\kvstore\server\environment;

use wfw\daemons\kvstore\server\KVSModes;
use wfw\engine\lib\PHP\types\UUID;

/**
 *  Session d'un utilisateur KVS loggé.
 */
final class KVSUserSession implements IKVSUserSession {
	/** @var string $_id */
	private $_id;
	/** @var IKVSUser $_user */
	private $_user;
	/** @var IKVSContainer $_container */
	private $_container;
	/** @var int|null $_defaultStorageMode */
	private $_defaultStorageMode;

	/**
	 * KVSUserSession constructor.
	 *
	 * @param IKVSContainer $container Container
	 * @param IKVSUser      $user      Utilisateur
	 * @param int|null              $defaultStorageMode Mode de stockage des infos par défaut.
	 */
	public function __construct(
		IKVSContainer $container,
		IKVSUser $user,
		?int $defaultStorageMode=null
	) {
		$this->_id = (string) UUID::v4();
		$this->_user = $user;
		$this->_container = $container;
		if(is_null($defaultStorageMode)){
			$this->_defaultStorageMode = $container->getDefaultStorageMode();
		}else{
			if(KVSModes::existsValue($defaultStorageMode)){
				$this->_defaultStorageMode = $defaultStorageMode;
			}else{
				throw new \InvalidArgumentException("Unknwown storage mode : $defaultStorageMode");
			}
		}
	}

	/**
	 * @return string Identifiant de la session
	 */
	public function getId(): string {
		return $this->_id;
	}

	/**
	 * @return IKVSUser Utilisateur associé à la session
	 */
	public function getUser(): IKVSUser {
		return $this->_user;
	}

	/**
	 * @return IKVSContainer Container auquel l'utilisateur est connecté.
	 */
	public function getContainer(): IKVSContainer {
		return $this->_container;
	}

	/**
	 * @return int Mode de stockage des données par défaut
	 */
	public function getDefaultStorageMode(): int {
		return $this->_defaultStorageMode;
	}
}