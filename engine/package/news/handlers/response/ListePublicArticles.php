<?php

namespace wfw\engine\package\news\handlers\response;

use wfw\engine\core\cache\ICacheSystem;
use wfw\engine\core\data\model\IArraySorter;
use wfw\engine\core\data\model\ModelSorter;
use wfw\engine\core\data\specification\ISpecification;
use wfw\engine\core\lang\ITranslator;
use wfw\engine\core\response\IResponse;
use wfw\engine\core\response\IResponseHandler;
use wfw\engine\core\view\IView;
use wfw\engine\core\view\IViewFactory;
use wfw\engine\package\general\views\error\Error;
use wfw\engine\package\news\cache\NewsCacheKeys;
use wfw\engine\package\news\data\model\ArticleSorter;
use wfw\engine\package\news\data\model\DTO\Article;
use wfw\engine\package\news\data\model\IArticleModelAccess;
use wfw\engine\package\news\lib\helper\ArticleAdapter;

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
	/** @var null|ISpecification $_spec */
	private $_spec;
	/** @var ICacheSystem $_cache */
	private $_cache;
	/** @var ITranslator $_translator */
	private $_translator;
	/** @var bool $_returnErrorView */
	private $_returnErrorView;

	/**
	 * ListeHandler constructor.
	 *
	 * @param IViewFactory        $factory
	 * @param IArticleModelAccess $access
	 * @param ICacheSystem        $cache
	 * @param ITranslator         $translator
	 * @param string              $view
	 * @param int                 $offset
	 * @param int                 $length
	 * @param null|ISpecification $spec
	 * @param bool $returnErrorView Si false, the error view is passed to the $view at offset 0 in
	 *                              the array given as argument, else an Error view is returned by
	 *                              the handler, not $view
	 * @param IArraySorter[]      $sorters
	 */
	public function __construct(
		IViewFactory $factory,
		IArticleModelAccess $access,
		ICacheSystem $cache,
		ITranslator $translator,
		string $view,
		int $offset = 0,
		int $length = 0,
		?ISpecification $spec=null,
		bool $returnErrorView=true,
		IArraySorter... $sorters
	){
		$this->_returnErrorView = $returnErrorView;
		$this->_translator = $translator;
		$this->_factory = $factory;
		$this->_access = $access;
		$this->_length = $length;
		$this->_offset = $offset;
		$this->_cache = $cache;
		$this->_view = $view;
		$this->_spec = $spec;
		$this->_sorters = (count($sorters)>0) ? $sorters : [new ArticleSorter()];
	}

	/**
	 * @param IResponse $response Réponse créer par l'ActionHandler
	 * @return IView Vue à retourner au client
	 */
	public function handleResponse(IResponse $response): IView {
		$sorter = new ModelSorter($this->_offset,$this->_length,...$this->_sorters);
		$spec = $this->_spec;
		$key = NewsCacheKeys::ROOT."/$this->_view/$sorter/$spec";

		if(!$this->_cache->contains($key)){
			try{
				$this->_cache->set($key,$res = $this->convert(
					...$this->_access->getArticleToDisplayInPublic($sorter, $spec)
				));
			}catch(\Error | \Exception $e){
				$err = new Error($this->_translator->getAndTranslate(
					"server/engine/package/news/service_unavailable/LIST"
				),503);
				if($this->_returnErrorView) return $err;
				else{
					$res = [$err];
					http_response_code(503);
				}
			}
		} else $res = $this->_cache->get($key);
		return $this->_factory->create($this->_view,[$res]);
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