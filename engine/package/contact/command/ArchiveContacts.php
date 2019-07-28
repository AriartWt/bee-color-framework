<?php
namespace wfw\engine\package\contact\command;

/**
 * Archive le(s) prises de contacts spécifiées
 */
final class ArchiveContacts extends ContactCommand {
	/** @var string[] $_ids */
	private $_ids;

	/**
	 * ArchiveContact constructor.
	 *
	 * @param string $userId Utilisateur a l'origine de la demande d'archivage
	 * @param string ...$ids Liste des identifiants
	 */
	public function __construct(?string $userId=null, string... $ids) {
		parent::__construct($userId);
		$this->_ids =$ids;
	}

	/**
	 * @return string[]
	 */
	public function getIds(): array {
		return $this->_ids;
	}
}