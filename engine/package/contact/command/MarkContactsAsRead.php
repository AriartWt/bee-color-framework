<?php
namespace wfw\engine\package\contact\command;

/**
 * Marque une liste de prises de contact comme lue
 */
final class MarkContactsAsRead extends ContactCommand {
	/** @var string $_userId */
	private $_userId;
	/** @var string[] $_ids */
	private $_ids;

	/**
	 * UnarchiveContact constructor.
	 *
	 * @param string $userId Identifiant de l'utilisateur à l'origine de la demande
	 * @param string ...$ids Identifiants des prises de contacts à marquer comme lu
	 */
	public function __construct(string $userId, string... $ids) {
		parent::__construct();
		$this->_userId = $userId;
		$this->_ids = $ids;
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