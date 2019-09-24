<?php
namespace wfw\daemons\modelSupervisor\server\environment;

use wfw\engine\lib\PHP\types\UUID;

/**
 *  Session du MSServer
 */
final class MSServerSession implements IMSServerSession {
	/** @var string $_id */
	private $_id;
	/** @var IMSServerUser $_user */
	private $_user;

	/**
	 * MSServerSession constructor.
	 *
	 * @param IMSServerUser $user Utilisateur pour lequel on crée une session
	 */
	public function __construct(IMSServerUser $user) {
		$this->_id = (string) new UUID(UUID::V4);
		$this->_user = $user;
	}

	/**
	 * @return string Identifiant de la session
	 */
	public function getId(): string {
		return $this->_id;
	}

	/**
	 * @return IMSServerUser Utilisateur associé à la session
	 */
	public function getUser(): IMSServerUser {
		return $this->_user;
	}
}