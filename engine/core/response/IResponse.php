<?php
namespace wfw\engine\core\response;

/**
 * Réponse d'un action handler suite à l'appel de handle(IAction)
 */
interface IResponse {
	/**
	 * @return mixed Données de la réponse
	 */
	public function getData();
}