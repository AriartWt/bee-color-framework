<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 26/04/18
 * Time: 11:44
 */

namespace wfw\engine\package\news\command;

use wfw\engine\core\command\Command;

/**
 * Met un article hors ligne
 */
final class PutArticlesOffline extends Command
{
	/** @var string[] $_articleIds */
	private $_articleIds;
	/** @var string $_userId */
	private $_userId;

	/**
	 * PutArticleOffline constructor.
	 *
	 * @param string   $userIds Ide6ntifiant de l'utilisateur mettant l'article hors ligne.
	 * @param string[] $ids     Identifiant des articles à mettre hors-ligne
	 */
	public function __construct(string $userIds, string... $ids) {
		parent::__construct();
		$this->_articleIds = $ids;
		$this->_userId = $userIds;
	}

	/**
	 * @return string[]
	 */
	public function getArticleIds(): array { return $this->_articleIds; }

	/**
	 * @return string
	 */
	public function getUserId(): string { return $this->_userId; }
}