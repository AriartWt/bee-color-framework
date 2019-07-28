<?php
namespace wfw\engine\package\news\command;

use wfw\engine\core\command\Command;

/**
 * Met un article hors ligne
 */
final class PutArticlesOffline extends Command {
	/** @var string[] $_articleIds */
	private $_articleIds;

	/**
	 * PutArticleOffline constructor.
	 *
	 * @param string   $userIds Ide6ntifiant de l'utilisateur mettant l'article hors ligne.
	 * @param string[] $ids     Identifiant des articles à mettre hors-ligne
	 */
	public function __construct(string $userIds, string... $ids) {
		parent::__construct($userIds);
		$this->_articleIds = $ids;
	}

	/**
	 * @return string[]
	 */
	public function getArticleIds(): array {
		return $this->_articleIds;
	}
}