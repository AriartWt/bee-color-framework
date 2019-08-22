<?php
namespace wfw\engine\package\users\command;

/**
 * Remove à user
 */
final class RemoveUsers extends UserCommand {
	/** @var string[] $_users */
	private $_users;
	
	/**
	 * RemoveUsers constructor.
	 *
	 * @param string   $removerId Utilisateur demandant la suppression
	 * @param string[] $ids       Liste de sutilisateurs à supprimer
	 */
	public function __construct(string $removerId, string... $ids) {
		parent::__construct($removerId);
		$this->_users = $ids;
	}
	
	/**
	 * @return string[]
	 */
	public function getUsers(): array {
		return $this->_users;
	}
}