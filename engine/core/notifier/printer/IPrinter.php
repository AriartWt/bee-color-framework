<?php
namespace wfw\engine\core\notifier\printer;

use wfw\engine\core\notifier\IMessage;

/**
 * Printer de messages pour un notifier.
 */
interface IPrinter {
	/**
	 * @param IMessage $message Message à printer.
	 * @return string Représentation d'un message
	 */
	public function print(IMessage $message):string;
}