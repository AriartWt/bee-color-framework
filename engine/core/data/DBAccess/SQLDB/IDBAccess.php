<?php
namespace wfw\engine\core\data\DBAccess\SQLDB;

use wfw\engine\core\data\query\ISQLQuery;

/**
 *  Définit l'accés à une base de données
 */
interface IDBAccess {
	/**
	 *  Execute une requête SQL et retourne le résultat
	 *
	 * @param ISQLQuery $query Requête à executer
	 *
	 * @return mixed
	 */
	public function execute(ISQLQuery $query);

	/**
	 *  Permet de savoir si l'objet courant est en cours de transaction.
	 * @return bool
	 */
	public function inTransaction():bool;

	/**
	 *  Démarre une transaction
	 */
	public function beginTransaction();

	/**
	 *  Annule les changements effectués depuis le dernier appel à beginTransaction
	 */
	public function rollBack();

	/**
	 *  Met fin à une transaction en sauvegardant toutes les modifications effectuées depuis le dernier appel à beginTransaction
	 */
	public function commit();
}