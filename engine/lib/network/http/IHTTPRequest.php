<?php
namespace wfw\engine\lib\network\http;

/**
 *  requette HTTP
 */
interface IHTTPRequest {
	/**
	 *  Envoie une requête HTTP et retourne la réponse
	 * @return string
	 */
	public function send():string;
}