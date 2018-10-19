<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 26/04/18
 * Time: 11:48
 */

namespace wfw\engine\package\news\command\handlers;

use wfw\engine\core\command\ICommand;
use wfw\engine\core\command\ICommandHandler;
use wfw\engine\package\news\command\ArticleCommand;
use wfw\engine\package\news\command\errors\ArticleNotFound;
use wfw\engine\package\news\domain\Article;
use wfw\engine\package\news\domain\repository\IArticleRepository;

/**
 * Commande handler de base pour un article.
 */
abstract class ArticleCommandHandler implements ICommandHandler
{
	/** @var IArticleRepository $_repos */
	private $_repos;

	/**
	 * ArticleCommandHandler constructor.
	 *
	 * @param IArticleRepository $repository
	 */
	public function __construct(IArticleRepository $repository) {
		$this->_repos = $repository;
	}

	/**
	 * Obtient un article
	 * @param string $id
	 * @return Article
	 * @throws ArticleNotFound
	 */
	protected function get(string $id):Article{
		$article = $this->_repos->get($id);
		if(is_null($article)) throw new ArticleNotFound($id);
		return $article;
	}

	/**
	 * @return IArticleRepository
	 */
	protected function repos():IArticleRepository{
		return $this->_repos;
	}
}