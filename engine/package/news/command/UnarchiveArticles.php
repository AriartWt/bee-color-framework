<?php
namespace wfw\engine\package\news\command;

/**
 * Désarchive un article
 */
final class UnarchiveArticles extends ArticleCommand {
	/** @var string[] $_articleId */
	private $_articleId;

	/**
	 * UnarchiveArticle constructor.
	 *
	 * @param string $userId     identifiant de l'utilisateur ayant demandé le désarchivage
	 * @param string[] $articleIds Identifiant de l'article
	 */
	public function __construct(string $userId,string... $articleIds) {
		parent::__construct($userId);
		$this->_articleId = $articleIds;
	}

	/**
	 * @return string[]
	 */
	public function getArticleIds(): array {
		return $this->_articleId;
	}
}