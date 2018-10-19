<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 11/09/18
 * Time: 16:28
 */

namespace wfw\engine\core\response;

use wfw\engine\core\view\IView;
use wfw\engine\core\view\IViewFactory;

/**
 * Réponse handler par défaut. Se contente de créer la vue désignée viewClass
 */
abstract class DefaultResponseHandler implements IResponseHandler{
	/** @var IViewFactory $_factory */
	private $_factory;
	/** @var string $_class */
	private $_class;

	/**
	 * DefaultResponseHandler constructor.
	 *
	 * @param IViewFactory $factory
	 * @param string       $viewClass
	 */
	public function __construct(IViewFactory $factory, string $viewClass) {
		if(!is_a($viewClass,IView::class,true))
			throw new \InvalidArgumentException("$viewClass doesn't implements ".IView::class);
		$this->_factory = $factory;
		$this->_class = $viewClass;
	}

	/**
	 * @param IResponse $response Réponse créer par l'ActionHandler
	 * @return IView Vue à retourner au client
	 */
	public function handleResponse(IResponse $response): IView {
		return $this->_factory->create($this->_class,[$response->getData()]);
	}
}