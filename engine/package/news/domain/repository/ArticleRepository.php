<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 23/04/18
 * Time: 10:54
 */

namespace wfw\engine\package\news\domain\repository;

use wfw\engine\core\command\ICommand;
use wfw\engine\core\domain\repository\IAggregateRootRepository;
use wfw\engine\lib\PHP\types\UUID;
use wfw\engine\package\news\domain\Article;

/**
 * Repository d'articles
 */
final class ArticleRepository implements IArticleRepository
{
	/** @var IAggregateRootRepository $_repos */
	private $_repos;

	/**
	 * ArticleRepository constructor.
	 *
	 * @param IAggregateRootRepository $repos Repository
	 */
	public function __construct(IAggregateRootRepository $repos){
		$this->_repos = $repos;
	}

	/**
	 * Obtient l'article d'identifiant $id
	 *
	 * @param string $id
	 * @return null|Article
	 */
	public function get(string $id): ?Article {
		/** @var Article|null $article */
		$article = $this->_repos->get(new UUID(UUID::V6,$id));
		return $article;
	}

	/**
	 * @param Article  $article Article à ajouter/modifier
	 * @param ICommand $command Commande ayant entraînée la création
	 */
	public function add(Article $article,ICommand $command): void{
		$this->_repos->add($article,$command);
	}

	/**
	 * @param Article  $article Article à supprimer
	 * @param ICommand $command Commande ayant entraîné la modification
	 */
	public function edit(Article $article,ICommand $command): void{
		$this->_repos->modify($article,$command);
	}
	
	/**
	 * Retourne tous les articles correspondants aux identifiants
	 *
	 * @param string[] $ids Liste d'identifiants d'articles
	 * @return Article[]
	 */
	public function getAll(string... $ids): array {
		$uuids = [];
		foreach($ids as $id){$uuids[] = new UUID(UUID::V6,$id);}
		return $this->_repos->getAll(...$uuids);
	}
}