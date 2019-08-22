<?php
namespace wfw\engine\core\query\result;

/**
 *  Query result listener
 */
interface IQueryResultListener {
	/**
	 * Méthode appelée lors de la reception d'un événement
	 * @param IQueryResult $e Evenement reçu
	 */
	public function recieveQueryResult(IQueryResult $e):void;
}