<?php
namespace wfw\engine\core\query\result;

/**
 *  Dispatche un événement ou un groupe d'événements
 */
interface IQueryResultDispatcher {
	/**
	 * Dispatche un événement
	 * @param IQueryResult $e Evenement à dispatcher
	 */
	public function dispatchQueryResult(IQueryResult $e):void;

	/**
	 * @param IQueryResult[] $events
	 */
	public function dispatchAllQueryResults(IQueryResult... $events):void;
}