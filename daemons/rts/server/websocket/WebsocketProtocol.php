<?php

namespace wfw\daemons\rts\server\websocket;

/**
 * This class is based on Xaraknide's websocket lib available on github :
 * https://github.com/Xaraknid/PHP-Websockets/blob/revision/core/websockets.php
 *
 * Some base functionnalities have been modified to fit RTS's specs, but most of the logic is the
 * same.
 */
final class WebsocketProtocol implements IWebsocketProtocol {
	/**
	 * @param IWebsocketEvent[] $events Evenements à dispatcher
	 */
	public function dispatch(IWebsocketEvent... $events): void {
		// TODO: Implement dispatch() method.
	}

	/**
	 * Accepte une nouvelle connexion
	 *
	 * @param resource $socket Socket network
	 */
	public function accept($socket): void {
		// TODO: Implement accept() method.
	}

	/**
	 * Vérifie toutes les sockets et dispatch les événements en fonction des lectures/écritures dans
	 * les socket clients
	 */
	public function process(): void {
		// TODO: Implement process() method.
	}
}