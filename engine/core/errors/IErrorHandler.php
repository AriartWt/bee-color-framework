<?php
namespace wfw\engine\core\errors;

/**
 * Gestionnaire d'erreurs.
 */
interface IErrorHandler {
	/**
	 *  Initialise les différents handlers
	 */
	public function handle():void;
}