<?php
namespace wfw\engine\core\router;

use wfw\engine\core\action\IAction;
use wfw\engine\core\request\IRequest;

/**
 * Router principal. Permet la résolution d'urls.
 */
interface IRouter {
	/**
	 * Obtient une URL formattée en fonction du paramètrage du Router.
	 * @param string $url url réelle relative
	 * @return string URL finale absolue
	 */
	public function url(string $url=''):string;

	/**
	 * Obtient une URL résolue relativement au dossier public (webroot)
	 * @param string $url URL relative
	 * @return string
	 */
	public function webroot(string $url=''):string;

	/**
	 * Construit une action à partir d'une requête
	 * @param IRequest $request Requête
	 * @return IAction Action résultante
	 */
	public function parse(IRequest $request):IAction;

	/**
	 * @param string $lang Ajoute une langue au router afin qu'elle soit reconnue
	 */
	public function addLang(string $lang):void;

	/**
	 * Connecte deux URL.
	 *
	 * @param string $redir URL à connecter
	 * @param string $url   URL de connexion
	 * @param array  $params (optionnal) connection params (depends oo the implementation)
	 */
	public function addConnection(string $redir, string $url,array $params=[]):void;

	/*
	 * Rétabli les paramètres du routeurs qui doivent changer à chaque requêtes (permet d'utiliser
	 * un système de cache pour la résolution des urls)
	 */
	public function reset():void;
}