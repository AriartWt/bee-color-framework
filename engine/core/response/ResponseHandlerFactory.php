<?php
namespace wfw\engine\core\response;

use wfw\engine\core\app\factory\IGenericAppFactory;

/**
 * Crée un ResponseHandler en utilisant Dice.
 */
final class ResponseHandlerFactory implements IResponseHandlerFactory {
	/** @var IGenericAppFactory $_factory */
	private $_factory;

	/**
	 * ResponseHandlerFactory constructor.
	 *
	 * @param IGenericAppFactory $factory Factory pour la création du handler
	 */
	public function __construct(IGenericAppFactory $factory) {
		$this->_factory = $factory;
	}

	/**
	 * Crée un handler $class avec les paramètres $params
	 *
	 * @param string $class  handler
	 * @param array  $params Paramètres de construction du handler
	 * @return IResponseHandler
	 */
	public function create(string $class, array $params = []): IResponseHandler {
		return $this->_factory->create($class,$params,[IResponseHandler::class]);
	}
}