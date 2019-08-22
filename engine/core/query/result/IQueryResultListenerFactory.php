<?php
namespace wfw\engine\core\query\result;

/**
 * Factory de QueryResultiLstener
 */
interface IQueryResultListenerFactory {
	/**
	 * @param string $listenerClass Listener à créer
	 * @param array  $params Paramètres de création
	 * @return IQueryResultListener
	 */
	public function buildQueryResultListener(string $listenerClass, array $params=[]):IQueryResultListener;
}