<?php
namespace wfw\engine\core\request;

/**
 * Requête HTTP reçue.
 */
interface IRequest {
	public const GET="GET";
	public const PUT="PUT";
	public const POST="POST";
	public const HEAD="HEAD";
	public const PATCH="PATCH";
	public const TRACE="TRACE";
	public const DELETE="DELETE";
	public const OPTIONS="OPTIONS";
	public const CONNECT="CONNECT";

	/**
	 * @return bool True si la requête est AJAX, false sinon.
	 */
	public function isAjax():bool;

	/**
	 * @return string IP du client
	 */
	public function getIP():string;

	/**
	 * @return string URI
	 */
	public function getURI():string;

	/**
	 * @return string URL
	 */
	public function getURL():string;

	/**
	 * @return string Méthode HTTP
	 */
	public function getMethod():string;

	/**
	 * @param array $availables Si spécifié, sert de filtre our ne retourner que les langues
	 *                          contenues dans availables
	 * @return array Langues acceptées par le client.
	 */
	public function getAcceptedLanguages(array $availables=[]): array;

	/**
	 * @return null|string Token CSRF si fourni
	 */
	public function getCSRFToken():?string;

	/**
	 * @return IRequestData Données de la requête
	 */
	public function getData():IRequestData;
}