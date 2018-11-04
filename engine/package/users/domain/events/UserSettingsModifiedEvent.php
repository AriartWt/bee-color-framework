<?php
namespace wfw\engine\package\users\domain\events;

use wfw\engine\lib\PHP\types\UUID;

/**
 *  Paramètres utilisateur modifiés
 */
final class UserSettingsModifiedEvent extends UserEvent {
	/** @var array $_settings */
	private $_settings;
	
	/**
	 *  UserSettingsModifiedEvent constructor.
	 *
	 * @param UUID   $userId   Identifiant de l'utilisateur
	 * @param array  $settings Tableau de clé/valeur
	 * @param string $modifierID Identifiant de l'utilisateur modifiant les paramètres
	 */
	public function __construct(UUID $userId, array $settings,string $modifierID) {
		parent::__construct($userId,$modifierID);
		$this->_settings = $settings;
	}

	/**
	 * @return array
	 */
	public function getSettings():array{
		return $this->_settings;
	}
}