<?php
namespace wfw\engine\core\action;

use wfw\engine\core\request\IRequest;

/**
 * Action
 */
interface IAction {
	/**
	 * @return IRequest
	 */
	public function getRequest():IRequest;

	/**
	 * @return string Chemin interne permettant de determiner le handler
	 */
	public function getInternalPath():string;

	/**
	 * @return null|string Langue
	 */
	public function getLang():?string;
}