<?php

namespace wfw\daemons\rts\server\websocket;

use wfw\engine\lib\PHP\errors\IllegalInvocation;

/**
 * Si un listener hérite de cette classe, il devra implémenter une méthode
 * applyEventClassName(EventClassName $e):void pour chacun des évènements qu'il est en mesure de
 * recevoir.
 * Cela permet d'utiliser le type hinting de PHP dans les noms de méthode, et de lancer une erreur
 * si un listener reçois un événement qu'il n'est pas censé recevoir
 */
abstract class WebsocketEventListener implements IWebsocketListener {
	/**
	 * @param IWebsocketEvent $event Evenement
	 */
	public final function applyWebsocketEvent(IWebsocketEvent $event): void {
		$class = (new \ReflectionClass($event))->getShortName();
		$method = "apply$class";
		if(!method_exists($this,$method)) throw new IllegalInvocation(
			"Can't handle ".get_class($event)." : current listener doesn't implements $method()"
		);
		$this->$method($event);
	}
}