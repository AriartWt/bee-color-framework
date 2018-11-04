<?php
namespace wfw\engine\package\contact\command;

/**
 * Archive le(s) prises de contacts spécifiées
 */
final class ArchiveContacts extends ContactCommand {
	/** @var string $_userId */
	private $_userId;
	/** @var string[] $_ids */
	private $_ids;

	/**
	 * ArchiveContact constructor.
	 *
	 * @param string $userId Utilisateur a l'origine de la demande d'archivage
	 * @param string ...$ids Liste des identifiants
	 */
	public function __construct(string $userId, string... $ids) {
		parent::__construct();
		$this->_userId = $userId;
		$this->_ids =$ids;
	}

	/**
	 * @return string
	 */
	public function getUserId(): string {
		return $this->_userId;
	}

	/**
	 * @return string[]
	 */
	public function getIds(): array {
		return $this->_ids;
	}
}