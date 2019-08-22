<?php
namespace wfw\engine\core\domain\events;

/**
 * Factory de DomainEventiLstener
 */
interface IDomainEventListenerFactory {
	/**
	 * @param string $listenerClass Listener à créer
	 * @param array  $params Paramètres de création
	 * @return IDomainEventListener
	 */
	public function buildDomainEventListener(string $listenerClass, array $params=[]):IDomainEventListener;
}