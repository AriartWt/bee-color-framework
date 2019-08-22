<?php
namespace wfw\engine\core\query\result;

use wfw\engine\core\app\factory\IGenericAppFactory;

/**
 * Factroy de QueryResultListener basée sur Dice
 */
final class QueryResultListenerFactory implements IQueryResultListenerFactory {
	/** @var IGenericAppFactory $_factory */
	private $_factory;

	/**
	 * QueryResultListenerFactory constructor.
	 *
	 * @param IGenericAppFactory $factory
	 */
	public function __construct(IGenericAppFactory $factory) {
		$this->_factory = $factory;
	}

	/**
	 * @param string $listenerClass Listener à créer
	 * @param array  $params Paramètres de création
	 * @return IQueryResultListener
	 */
	public function buildQueryResultListener(string $listenerClass, array $params=[]): IQueryResultListener {
		return $this->_factory->create($listenerClass,$params,[IQueryResultListener::class]);
	}
}