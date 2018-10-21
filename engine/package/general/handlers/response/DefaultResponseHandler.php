<?php
/**
 * Created by PhpStorm.
 * User: ariart
 * Date: 21/10/18
 * Time: 17:12
 */

namespace engine\package\general\handlers\response;

use wfw\engine\core\response\IResponse;
use wfw\engine\core\response\IResponseHandler;
use wfw\engine\core\view\IView;
use wfw\engine\core\view\IViewFactory;

/**
 * Response handler de base pour la construction d'une vue
 */
abstract class DefaultResponseHandler implements IResponseHandler {
	/** @var IViewFactory $_factory */
	private $_factory;

	/**
	 * DefaultResponseHandler constructor.
	 *
	 * @param IViewFactory $factory Factory
	 */
	public function __construct(IViewFactory $factory) {
		$this->_factory = $factory;
	}

	/**
	 * @return string
	 */
	protected abstract function viewClass():string;

	/**
	 * @return array Construct params pour la vue à créer
	 */
	protected function viewParams():array{
		return [];
	}

	/**
	 * @param IResponse $response Réponse créer par l'ActionHandler
	 * @return IView Vue à retourner au client
	 */
	public function handleResponse(IResponse $response): IView {
		return $this->_factory->create(
			$this->viewClass(),
			array_merge([$response],$this->viewParams())
		);
	}
}