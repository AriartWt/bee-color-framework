<?php
namespace wfw\engine\core\security;
use wfw\engine\core\response\IResponse;

/**
 * Permission d'acces.
 */
interface IAccessPermission {
	/**
	 * @return bool Permission accordée ou non.
	 */
	public function isGranted():bool;

	/**
	 * @return null|string Code
	 */
	public function getCode():?string;

	/**
	 * @return null|string Message
	 */
	public function getMessage():?string;

	/**
	 * @return null|IResponse Réponse
	 */
	public function getResponse():?IResponse;
}