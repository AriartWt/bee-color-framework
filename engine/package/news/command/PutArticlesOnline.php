<?php
namespace wfw\engine\package\news\command;

/**
 * Class PutArticleOnline
 *
 * @package wfw\engine\package\news\command
 */
final class PutArticlesOnline extends ArticleCommand {
	/** @var string[] $_articleIds */
	private $_articleIds;
	/** @var string $_userId */
	private $_userId;

	/**
	 * PutArticleOnline constructor.
	 *
	 * @param string   $userIds Identifiant de l'utilisateur mettant l'article en ligne
	 * @param string[] $ids     Identifiants des articles à mettre en ligne
	 */
	public function __construct(string $userIds, string ...$ids) {
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