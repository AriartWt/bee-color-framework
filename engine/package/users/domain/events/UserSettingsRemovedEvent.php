<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 11/12/17
 * Time: 04:02
 */

namespace wfw\engine\package\users\domain\events;

use wfw\engine\lib\PHP\types\UUID;

/**
 *  Clé de paramètre sutilisateur supprimée
 */
final class UserSettingsRemovedEvent extends UserEvent {
	/** @var array $_settings */
	private $_settings;
	
	/**
	 *  UserSettingsRemovedEvent constructor.
	 *
	 * @param UUID   $userId   Identifiant de l'utilisateur
	 * @param array  $settings Liste des clés à supprimer
	 * @param string $remover  Identifiant de l'utilisateur supprimant le paramètre
	 */
	public function __construct(UUID $userId, array $settings, string $remover) {
		parent::__construct($userId,$remover);
		$this->_settings = $settings;
	}

	/**
	 * @return array
	 */
	public function getSettings():array{
		return $this->_settings;
	}
}