<?php
namespace wfw\cli\tester\contexts;
use wfw\engine\core\app\context\IWebAppContext;

/**
 * Contexte de tests
 */
interface ITestsEnvironment {
	/**
	 * Initialise ou réinitialise l'environnement de tests avec les arguments spécifiés
	 * @param array $args
	 */
	public function init(array $args=[]):void;

	/**
	 * @param string $obj Classe de l'objet à instancier
	 * @param array $params Liste des paramètres à passer au constructeur
	 * @return mixed
	 */
	public function create(string $obj, array $params=[]);

	/**
	 * Retourne le webAppContext courant
	 * @return IWebAppContext
	 */
	public function getAppContext():IWebAppContext;
}