<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 30/09/18
 * Time: 16:13
 */

namespace wfw\engine\package\contact\command;

/**
 * Marque une liste de prises de contact comme non lue
 */
final class MarkContactsAsUnread extends ContactCommand {
	/** @var string $_userId */
	private $_userId;
	/** @var string[] $_ids */
	private $_ids;

	/**
	 * UnarchiveContact constructor.
	 *
	 * @param string $userId Identifiant de l'utilisateur à l'origine de la demande
	 * @param string ...$ids Identifiants des prises de contacts à marquer comme non lu
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