<?php
namespace wfw\engine\package\news\command;

/**
 * Archive un article
 */
final class ArchiveArticles extends ArticleCommand {
	/** @var string[] $_articleIds */
	private $_articleIds;
	/** @var string $_userId */
	private $_userId;

	/**
	 * ArchiveArticle constructor.
	 *
	 * @param string   $userIds    Identifiant de l'utilisateur demandant l'archivage
	 * @param string[] $articleIds Identifiant de l'article
	 */
	public function __construct(string $userIds, string... $articleIds) {
		parent::__construct();
		$this->_articleIds = $articleIds;
		$this->_userId = $userIds;
	}

	/**
	 * @return string[]
	 */
	public function getArticleIds(): array { return $this->_articleIds; }

	/**
	 * @return string
	 */
	public function getUserId(): string{ return $this->_userId; }
}