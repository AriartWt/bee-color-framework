<?php

namespace wfw\engine\package\news\handlers\response;

use wfw\engine\core\data\model\IArraySorter;
use wfw\engine\core\data\model\ModelSorter;
use wfw\engine\core\response\IResponse;
use wfw\engine\core\response\IResponseHandler;
use wfw\engine\core\view\IView;
use wfw\engine\core\view\IViewFactory;
use wfw\engine\package\news\data\model\ArticleSorter;
use wfw\engine\package\news\data\model\DTO\Article;
use wfw\engine\package\news\data\model\IArticleModelAccess;
use wfw\engine\package\news\lib\helper\ArticleAdapter;
use wfw\site\package\web\views\news\liste\Liste;

/**
 * Helper pour la création d'une page d'articles publics.
 * Passe à la vue $view une liste de $length articles converti en ArticleAdapter triés dans l'ordre
 * des $sorters passés au cosntructeur (ArticleSorter si aucun n'est précisé) à partir de $offset en
 * envoyant une requête sur le model Article
 */
abstract class ListePublicArticles implements IResponseHandler{
	/** @var IViewFactory $_factory */
	private $_factory;
	/** @var IArticleModelAccess $_access */
	private $_access;
	/** @var int $_length */
	private $_length;
	/** @var string $_view */
	private $_view;
	/** @var int $_offset */
	private $_offset;
	/** @var array|IArraySorter[] $_sorters */
	private $_sorters;

	/**
	 * ListeHandler constructor.
	 *
	 * @param IViewFactory        $factory
	 * @param IArticleModelAccess $access
	 * @param string              $view
	 * @param int                 $offset
	 * @param int                 $length
	 * @param IArraySorter[]      $sorters
	 */
	public function __construct(
		IViewFactory $factory,
		IArticleModelAccess $access,
		string $view,
		int $offset = 0,
		int $length = 0,
		IArraySorter... $sorters
	){
		$this->_factory = $factory;
		$this->_access = $access;
		$this->_length = $length;
		$this->_offset = $offset;
		$this->_view = $view;
		$this->_sorters = (count($sorters)>0) ? $sorters : [new ArticleSorter()];
	}

	/**
	 * @param IResponse $response Réponse créer par l'ActionHandler
	 * @return IView Vue à retourner au client
	 */
	public function handleResponse(IResponse $response): IView {
		return $this->_factory->create(Liste::class,[$this->convert(
			...$this->_access->getArticleToDisplayInPublic(
				new ModelSorter($this->_offset,$this->_length,...$this->_sorters)
			)
		)]);
	}

	/**
	 * @param Article ...$articles
	 * @return ArticleAdapter[] articles
	 */
	private function convert(Article... $articles):array{
		$res =[];
		foreach($articles as $article){
			$res[]=new ArticleAdapter($article);
		}
		return $res;
	}
}