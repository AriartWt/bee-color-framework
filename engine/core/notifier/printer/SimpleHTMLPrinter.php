<?php
namespace wfw\engine\core\notifier\printer;

use wfw\engine\core\notifier\IMessage;

/**
 * Print simplement le message.
 */
final class SimpleHTMLPrinter implements IPrinter{
	/**
	 * @param IMessage $message Message à printer.
	 * @return string Représentation d'un message
	 */
	public function print(IMessage $message): string {
		return "<p class=\"".($message->getType()??'success')."\">$message</p>";
	}
}