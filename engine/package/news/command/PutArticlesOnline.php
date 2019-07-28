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

	/**
	 * PutArticleOnline constructor.
	 *
	 * @param string   $userIds Identifiant de l'utilisateur mettant l'article en ligne
	 * @param string[] $ids     Identifiants des articles à mettre en ligne
	 */
	public function __construct(string $userIds, string ...$ids) {
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