<?php
namespace wfw\engine\lib\data\string\json;

/**
 * Retourne les paramètres nécessaires à la transformation d'un objet lors de son passage
 * dans un IJSONEncoder
 */
interface IJSONPrintInfos {
	/**
	 * @return array string[](property names) : Liste des propriétés
	 *               à ne pas conserver.
	 */
	public function skipProperties():array;

	/**
	 * @return array property => callable/value : Pour chaque objet, une liste de propriétés
	 *               dont chaque callable est une fonction qui prend pour argument la valeur de la propriété.
	 */
	public function transformProperties():array;

	/**
	 * @return array Propriété -> valeur/callable : Pour chaque propriété, un callable ou une valuer.
	 *               Si callable : accepte en argument l'objet lui même.
	 */
	public function addProperties():array;
}