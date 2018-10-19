<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 21/06/18
 * Time: 16:16
 */

namespace wfw\engine\package\users\command;

/**
 * Active une liste d'utilisateur
 */
final class EnableUsers extends UserCommand{
	/** @var string $_enabler */
	private $_enabler;
	/** @var string[] $_users */
	private $_users;
	
	/**
	 * EnableUsers constructor.
	 *
	 * @param string $enabler  Identifiant de l'utilisateur ayant demandé l'activation
	 * @param string[] $users Liste des utilisateurs à activer
	 */
	public function __construct(string $enabler, string... $users) {
		parent::__construct();
		$this->_enabler = $enabler;
		$this->_users = $users;
	}
	
	/**
	 * @return string
	 */
	public function getEnabler(): string {
		return $this->_enabler;
	}
	
	/**
	 * @return string[]
	 */
	public function getUsers(): array {
		return $this->_users;
	}
}