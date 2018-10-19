<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 26/04/18
 * Time: 12:07
 */

namespace wfw\engine\package\news\command;

/**
 * Désarchive un article
 */
final class UnarchiveArticles extends ArticleCommand
{
	/** @var string[] $_articleId */
	private $_articleId;
	/** @var string $_userId */
	private $_userId;

	/**
	 * UnarchiveArticle constructor.
	 *
	 * @param string $userId     identifiant de l'utilisateur ayant demandé le désarchivage
	 * @param string[] $articleIds Identifiant de l'article
	 */
	public function __construct(string $userId,string... $articleIds) {
		parent::__construct();
		$this->_articleId = $articleIds;
		$this->_userId = $userId;
	}

	/**
	 * @return string[]
	 */
	public function getArticleIds(): array { return $this->_articleId; }

	/**
	 * @return string
	 */
	public function getUserId(): string { return $this->_userId; }
}