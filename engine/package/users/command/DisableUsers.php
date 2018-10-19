<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 21/06/18
 * Time: 16:19
 */

namespace wfw\engine\package\users\command;

/**
 * Désactive une liste d'utilisateurs
 */
final class DisableUsers extends UserCommand{
	/** @var string[] $_users */
	private $_users;
	/** @var string $_disabler */
	private $_disabler;
	
	/**
	 * DisableUsers constructor.
	 *
	 * @param string   $disabler Utilisateur ayant demandé la désactivation
	 * @param string[] $users    Utilisateurs à désactiver
	 */
	public function __construct(string $disabler, string... $users) {
		parent::__construct();
		$this->_users = $users;
		$this->_disabler = $disabler;
	}
	
	/**
	 * @return string[]
	 */
	public function getUsers(): array {
		return $this->_users;
	}
	
	/**
	 * @return string
	 */
	public function getDisabler(): string {
		return $this->_disabler;
	}
}