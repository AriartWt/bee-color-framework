<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 10/08/18
 * Time: 16:46
 */

namespace wfw\daemons\rts\server\environment;

use wfw\engine\lib\PHP\types\UUID;

/**
 * Session d'un utilisateur RTS
 */
final class RTSSession implements IRTSSession{
	/** @var string $_id */
	private $_id;
	/** @var IRTSUser $_user */
	private $_user;

	/**
	 * MSServerSession constructor.
	 *
	 * @param IRTSUser $user Utilisateur pour lequel on crée une session
	 */
	public function __construct(IRTSUser $user) {
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
	 * @return IRTSUser Utilisateur associé à la session
	 */
	public function getUser(): IRTSUser {
		return $this->_user;
	}
}