<?php

namespace wfw\engine\package\news\handlers\response;

use wfw\engine\core\lang\ITranslator;
use wfw\engine\core\response\IResponse;
use wfw\engine\core\response\IResponseHandler;
use wfw\engine\core\security\data\rules\IsUUID;
use wfw\engine\core\view\IView;
use wfw\engine\core\view\IViewFactory;
use wfw\engine\package\general\views\error\Error;
use wfw\engine\package\news\data\model\IArticleModelAccess;
use wfw\site\package\web\views\news\article\Article;
use wfw\engine\package\news\lib\helper\ArticleAdapter;

/**
 * Class DisplayArticle
 *
 * @package wfw\engine\package\news\handlers\response
 */
abstract class DisplayArticle implements IResponseHandler {
	/** @var ITranslator $_translator */
	private $_translator;
	/** @var IViewFactory $_factory */
	private $_factory;
	/** @var IArticleModelAccess $_access */
	private $_access;
	/** @var string $_id */
	private $_id;

	/**
	 * DisplayArticle constructor.
	 *
	 * @param IViewFactory        $factory
	 * @param IArticleModelAccess $access
	 * @param ITranslator         $translator
	 * @param string              $id
	 * @param bool                $onlineOnly
	 */
	public function __construct(
		IViewFactory $factory,
		IArticleModelAccess $access,
		ITranslator $translator,
		string $id,
		bool $onlineOnly = true
	) {
		$this->_id = $id;
		$this->_access = $access;
		$this->_factory = $factory;
		$this->_translator = $translator;
	}

	/**
	 * @param IResponse $response Réponse créer par l'ActionHandler
	 * @return IView Vue à retourner au client
	 */
	public function handleResponse(IResponse $response): IView {
		$key = "server/engine/package/news";
		$id = explode("_",$this->_id);
		$id = $id[count($id)-1];
		if((new IsUUID("","uuid"))->applyTo(["uuid"=>$id])->satisfied()){
			try{
				$article = $this->_access->getById($id);
			}catch(\Error | \Exception $e){
				return new Error($this->_translator->getAndTranslate(
					"$key/service_unavailable/ARTICLE"
				),503);
			}
			if(!is_null($article)){
				if($article->isArchived()) return new Error($this->_translator->getAndTranslate(
					"$key/REMOVED"
				),404);
				if($article->isOnline()) return $this->_factory->create(
					Article::class,
					[new ArticleAdapter($article)]
				);
				else return new Error($this->_translator->getAndTranslate(
					"$key/OFFLINE"
				),404);
			}else return new Error($this->_translator->getAndTranslate(
				"$key/NOT_FOUND"
			),404);
		}else return new Error($this->_translator->getAndTranslate(
			"$key/NOT_FOUND"
		),404);
	}
}