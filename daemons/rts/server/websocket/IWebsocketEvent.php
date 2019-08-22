<?php

namespace wfw\daemons\rts\server\websocket;

/**
 * Evenement d'un websocket
 */
interface IWebsocketEvent {
	/**
	 * @return string Event Id
	 */
	public function getId():string;

	/**
	 * @return float event creation date in microseconds
	 */
	public function getCreationDate():float;

	/**
	 * @return string The socket id
	 */
	public function getSocketId():string;
}