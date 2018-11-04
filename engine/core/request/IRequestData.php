<?php
namespace wfw\engine\core\request;

use stdClass;

/**
 * Données de la requête
 */
interface IRequestData {
	public const GET = 1;
	public const POST = 2;
	public const FILES = 4;

	/**
	 * Retourne les données de la requête en fonction de $flag. Si des clés sont dupliquées, elles
	 * sont écrasées dans l'ordre : GET -> POST -> FILE où '->' = 'écrasé par'.
	 *
	 * @param int  $flag Données à récupérer
	 * @param bool $asArray (optionnel defaut : false) Si true: retourne le résultat sous forme de
	 *                      tableau, sinon stdClass
	 * @return stdClass|array
	 */
	public function get(int $flag = self::GET | self::POST | self::FILES, bool $asArray=false);

	/**
	 * Supprime des indexes dans les tableaux de paramètres
	 * @param int    $flag       Tableau ciblé
	 * @param string ...$indexes Indexes à supprimer
	 */
	public function remove(int $flag, string... $indexes):void;
}