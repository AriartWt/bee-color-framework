<?php
namespace wfw\engine\package\users\command;

/**
 * Active une liste d'utilisateur
 */
final class EnableUsers extends UserCommand{
	/** @var string[] $_users */
	private $_users;
	
	/**
	 * EnableUsers constructor.
	 *
	 * @param string $enabler  Identifiant de l'utilisateur ayant demandé l'activation
	 * @param string[] $users Liste des utilisateurs à activer
	 */
	public function __construct(string $enabler, string... $users) {
		parent::__construct($enabler);
		$this->_users = $users;
	}
	
	/**
	 * @return string[]
	 */
	public function getUsers(): array {
		return $this->_users;
	}
}