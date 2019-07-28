<?php
namespace wfw\engine\package\news\command;

/**
 * Archive un article
 */
final class ArchiveArticles extends ArticleCommand {
	/** @var string[] $_articleIds */
	private $_articleIds;

	/**
	 * ArchiveArticle constructor.
	 *
	 * @param string   $userIds    Identifiant de l'utilisateur demandant l'archivage
	 * @param string[] $articleIds Identifiant de l'article
	 */
	public function __construct(string $userIds, string... $articleIds) {
		parent::__construct($userIds);
		$this->_articleIds = $articleIds;
	}

	/**
	 * @return string[]
	 */
	public function getArticleIds(): array {
		return $this->_articleIds;
	}
}