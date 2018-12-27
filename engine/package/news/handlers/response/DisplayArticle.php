<?php

namespace wfw\engine\package\news\handlers\response;

use wfw\engine\core\action\IAction;
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
	/** @var IViewFactory $_factory */
	private $_factory;
	/** @var IArticleModelAccess $_access */
	private $_access;
	/** @var string $_notFoundMessage */
	private $_notFoundMessage;
	/** @var string $_offlineMessage */
	private $_offlineMessage;
	/** @var string $_deletedMessage */
	private $_deletedMessage;
	/** @var string $_id */
	private $_id;

	/**
	 * DisplayArticle constructor.
	 *
	 * @param IViewFactory        $factory
	 * @param IArticleModelAccess $access
	 * @param string              $id
	 * @param bool                $onlineOnly
	 * @param string              $notFoundMessage
	 * @param string              $offlineMessage
	 * @param string              $deletedMessage
	 */
	public function __construct(
		IViewFactory $factory,
		IArticleModelAccess $access,
		string $id,
		bool $onlineOnly = true,
		string $notFoundMessage = "Article not found !",
		string $offlineMessage = "This article is offline !",
		string $deletedMessage = "This article have been deleted !"
	) {
		$this->_id = $id;
		$this->_access = $access;
		$this->_factory = $factory;
		$this->_notFoundMessage = $notFoundMessage;
		$this->_offlineMessage = $offlineMessage;
		$this->_deletedMessage = $deletedMessage;
	}

	/**
	 * @param IResponse $response Réponse créer par l'ActionHandler
	 * @return IView Vue à retourner au client
	 */
	public function handleResponse(IResponse $response): IView {
		$id = explode("_",$this->_id);
		$id = $id[count($id)-1];
		if((new IsUUID("","uuid"))->applyTo(["uuid"=>$id])->satisfied()){
			$article = $this->_access->getById($id);
			if(!is_null($article)){
				if($article->isArchived()) return new Error($this->_deletedMessage,404);
				if($article->isOnline()) return $this->_factory->create(
					Article::class,
					[new ArticleAdapter($article)]
				);
				else return new Error($this->_offlineMessage,404);
			}else return new Error($this->_notFoundMessage,404);
		}else return new Error($this->_notFoundMessage,404);
	}
}