<?php

namespace wfw\engine\core\notifier\printer;

use wfw\engine\core\notifier\IMessage;

/**
 * Printer JSON
 */
class JSONPrinter implements IPrinter {
	/**
	 * @param IMessage $message Message à printer.
	 * @return string Représentation d'un message
	 */
	public function print(IMessage $message): string {
		return json_encode([
			"message" => (string) $message,
			"type" => $message->getType()??'success'
		]);
	}
}