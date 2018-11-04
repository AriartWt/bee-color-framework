<?php
namespace wfw\engine\core\data\query;

/**
 *  Requête de suppression
 */
interface IDeleteQuery extends ISQLQuery {
	/**
	 *  Source des données
	 *
	 * @param string|ISQLQuery $tableOrQuery Table ou requête SQL
	 *
	 * @return IDeleteQuery
	 */
	public function from($tableOrQuery):IDeleteQuery;

	/**
	 * @param Valeur      $value
	 * @param null|string $key
	 *
	 * @return IDeleteQuery
	 */
	public function addParam($value, ?string $key = null):IDeleteQuery;

	/**
	 *  Conditions
	 * @param string[] ...$conditions Conditions
	 *
	 * @return IDeleteQuery
	 */
	public function where(string ...$conditions):IDeleteQuery;

	/**
	 *  Permet d'ajouter des paramètres sous forme de tableau
	 *
	 * @param array $array Paramètres à ajouter
	 *
	 * @return IDeleteQuery
	 */
	public function addParams(array $array):IDeleteQuery;

	/**
	 * @param string   $type
	 * @param string[] ...$tablesAndCond
	 *
	 * @return IDeleteQuery
	 * @throws SQLQueryException
	 */
	public function join(string $type, string ...$tablesAndCond):IDeleteQuery;

	/**
	 *  Permet de limiter le nombre d'update
	 * @param int $nb     Nombre de résultat
	 *
	 * @return IDeleteQuery
	 */
	public function limit(int $nb):IDeleteQuery;
}