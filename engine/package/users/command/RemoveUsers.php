<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 20/06/18
 * Time: 16:46
 */

namespace wfw\engine\package\users\command;

/**
 * Remove à user
 */
final class RemoveUsers extends UserCommand {
	/** @var string[] $_users */
	private $_users;
	/** @var string $_removerId */
	private $_removerId;
	
	/**
	 * RemoveUsers constructor.
	 *
	 * @param string   $removerId Utilisateur demandant la suppression
	 * @param string[] $ids       Liste de sutilisateurs à supprimer
	 */
	public function __construct(string $removerId, string... $ids) {
		parent::__construct();
		$this->_users = $ids;
		$this->_removerId = $removerId;
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
	public function getRemoverId(): string {
		return $this->_removerId;
	}
}