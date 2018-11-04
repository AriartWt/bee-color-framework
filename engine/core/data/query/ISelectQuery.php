<?php
namespace wfw\engine\core\data\query;

/**
 *  Requête select
 */
interface ISelectQuery extends ISQLQuery {
	/**
	 *  Source des données
	 *
	 * @param string|ISQLQuery $tableOrQuery Table ou requête SQL
	 *
	 * @return ISelectQuery
	 */
	public function from($tableOrQuery):ISelectQuery;

	/**
	 *  Ordonne les résultats
	 *
	 * @param string[] ...$columnAndCond nom de colonne + self::ASC ou self::DESC
	 *
	 * @return ISelectQuery
	 */
	public function orderBy(string ...$columnAndCond):ISelectQuery;

	/**
	 *  Groupe des colonnes si leurs valeurs sont identiques
	 *
	 * @param string[] ...$columns Colonnes à grouper
	 *
	 * @return ISelectQuery
	 */
	public function groupBy(string ...$columns):ISelectQuery;

	/**
	 *  IDEM que WHERE mais permet de filtrer sur des fonctions
	 *
	 * @param string[] ...$havingClauses exemple : "SUM(colName) > 4"
	 *
	 * @return ISelectQuery
	 */
	public function having(string ...$havingClauses): ISelectQuery;

	/**
	 *  Permet de limiter le nombre d'affichages
	 * @param int $nb     Nombre de résultat
	 * @param int $offset Offset ( range = $offset / $nb + $offset )
	 *
	 * @return ISelectQuery
	 */
	public function limit(int $nb, int $offset=0):ISelectQuery;

	/**
	 * @param mixed       $value
	 * @param null|string $key
	 *
	 * @return ISelectQuery
	 */
	public function addParam($value, ?string $key = null):ISelectQuery;

	/**
	 *  Conditions
	 * @param string[] ...$conditions Conditions
	 *
	 * @return ISelectQuery
	 */
	public function where(string ...$conditions):ISelectQuery;

	/**
	 *  Permet d'ajouter des paramètres sous forme de tableau
	 *
	 * @param array $array Paramètres à ajouter
	 *
	 * @return ISelectQuery
	 */
	public function addParams(array $array):ISelectQuery;

	/**
	 * @param string   $type
	 * @param string[] ...$tablesAndCond
	 *
	 * @return ISelectQuery
	 * @throws SQLQueryException
	 */
	public function join(string $type, string ...$tablesAndCond):ISelectQuery;
}