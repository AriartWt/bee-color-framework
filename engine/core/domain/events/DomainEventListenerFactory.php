<?php
namespace wfw\engine\core\domain\events;

use wfw\engine\core\app\factory\IGenericAppFactory;

/**
 * Factroy de DomainEventListener basée sur Dice
 */
final class DomainEventListenerFactory implements IDomainEventListenerFactory {
	/** @var IGenericAppFactory $_factory */
	private $_factory;

	/**
	 * DomainEventListenerFactory constructor.
	 *
	 * @param IGenericAppFactory $factory
	 */
	public function __construct(IGenericAppFactory $factory) {
		$this->_factory = $factory;
	}

	/**
	 * @param string $listenerClass Listener à créer
	 * @param array  $params Paramètres de création
	 * @return IDomainEventListener
	 */
	public function build(string $listenerClass,array $params=[]): IDomainEventListener {
		return $this->_factory->create($listenerClass,$params,[IDomainEventListener::class]);
	}
}