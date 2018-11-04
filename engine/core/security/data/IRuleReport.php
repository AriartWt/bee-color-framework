<?php
namespace wfw\engine\core\security\data;

/**
 * Rapport d'execution d'une règle sur un jeu de données
 */
interface IRuleReport {
	/**
	 * @return array Liste des erreurs sous la forme clé=>message
	 */
	public function errors():array;

	/**
	 * @return string Message d'erreur global (s'il y en a)
	 */
	public function message():?string;

	/**
	 * @return bool True : la régle est satisfaite, false sinon
	 */
	public function satisfied():bool;
}