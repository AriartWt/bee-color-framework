<?php
namespace wfw\engine\package\users\command;

/**
 * Désactive une liste d'utilisateurs
 */
final class DisableUsers extends UserCommand{
	/** @var string[] $_users */
	private $_users;
	
	/**
	 * DisableUsers constructor.
	 *
	 * @param string   $disabler Utilisateur ayant demandé la désactivation
	 * @param string[] $users    Utilisateurs à désactiver
	 */
	public function __construct(string $disabler, string... $users) {
		parent::__construct($disabler);
		$this->_users = $users;
	}
	
	/**
	 * @return string[]
	 */
	public function getUsers(): array {
		return $this->_users;
	}
}