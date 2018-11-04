<?php
namespace wfw\engine\core\data\query;

/**
 *  Comportement minimum d'une requête SQL
 */
interface ISQLQuery {
	public const INNER_JOIN="INNER JOIN";//Condition vraie dans les deux tables
	public const CROSS_JOIN="CROSS JOIN";//Retourne l'association de chaque ligne de la première a chaque ligne de la deuxième
	public const LEFT_JOIN="LEFT JOIN";//Retourne les enregistrement de la table de gauche même si condition non vérifiée dans l'autre table
	public const RIGHT_JOIN="RIGHT JOIN";//IDem ais table droite
	public const FULL_JOIN="FULL JOIN";//jointure externe pour retourner les résultats quand la condition est vrai dans au moins une des 2 tables.
	public const SELF_JOIN="SELF JOIN";//Jointure d'une table sur elle même

	/**
	 *  Transforme la requête courant en chaine de caractères
	 * @return string
	 */
	public function __toString():string;

	/**
	 *  Retourne les paramètres de la requêtes (paramètres bindés) sour forme de tableau "clé","valeur"
	 * @return array
	 */
	public function getParams():array;

	/**
	 *  Compile la requête courante
	 * @return CompiledQuery
	 */
	public function compile():CompiledQuery;

	/**
	 *  Permet d'ajouter des paramètres à binder à la requête
	 * @param             $value Valeur à binder
	 * @param null|string $key   (optionnel) Si non précisé, l'ordre est pris en compte
	 */
	public function addParam($value,?string $key=null);

	/**
	 *  Ajoute des clauses where
	 *
	 * @param string[] ...$conditions Conditions (séparée par des AND, pour des 0R grouper les conditions
	 */
	public function where(string ...$conditions);

	/**
	 *  Jointure sur plusieurs tableas
	 *
	 * @param string   $type             Type de jointure
	 * @param string[] ...$tablesAndCond Tables et conditions de jointure
	 */
	public function join(string $type,string ...$tablesAndCond);
}