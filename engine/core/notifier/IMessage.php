<?php
namespace wfw\engine\core\notifier;

/**
 * Message à passer à un notifier
 */
interface IMessage {
	/**
	 * @return string Message
	 */
	public function __toString();

	/**
	 * @return null|string Type du message
	 */
	public function getType():?string;

	/**
	 * @return array Paramètres optionnels.
	 */
	public function getParams():array;
}