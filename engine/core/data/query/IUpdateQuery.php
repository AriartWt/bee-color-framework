<?php
namespace wfw\engine\core\data\query;

/**
 *  Requête UPDATE
 */
interface IUpdateQuery extends ISQLQuery {
	/**
	 *  Ajoutes des valeures à insérer dans un INSERT ou un UPDATE
	 *
	 * @param string[] ...$asserts Valeures à insérer dans un INSERT ou un UPDATE
	 *
	 * @return IUpdateQuery
	 */
	public function set(string ...$asserts):IUpdateQuery;

	/**
	 *  Source des données
	 *
	 * @param string|ISQLQuery $tableOrQuery Table ou requête SQL
	 *
	 * @return IUpdateQuery
	 */
	public function from($tableOrQuery):IUpdateQuery;

	/**
	 *  Permet de limiter le nombre d'update
	 * @param int $nb     Nombre de résultat
	 *
	 * @return IUpdateQuery
	 */
	public function limit(int $nb):IUpdateQuery;

	/**
	 *  Where clause
	 *
	 * @param string[] ...$conditions Conditions
	 *
	 * @return IUpdateQuery
	 * @throws SQLQueryException
	 */
	public function where(string ...$conditions):IUpdateQuery;

	/**
	 * @param Valeur      $value
	 * @param null|string $key
	 *
	 * @return IUpdateQuery
	 */
	public function addParam($value, ?string $key = null):IUpdateQuery;

	/**
	 *  Permet d'ajouter des paramètres sous forme de tableau
	 *
	 * @param array $array Paramètres à ajouter
	 *
	 * @return IUpdateQuery
	 */
	public function addParams(array $array):IUpdateQuery;

	/**
	 * @param string   $type
	 * @param string[] ...$tablesAndCond
	 *
	 * @return IUpdateQuery
	 * @throws SQLQueryException
	 */
	public function join(string $type, string ...$tablesAndCond):IUpdateQuery;
}