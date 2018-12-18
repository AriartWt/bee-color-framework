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
	 * The event propagation MUST be stopped.
	 */
	public function stopPropagation():void;

	/**
	 * @return bool True if the event propagation is stopped
	 */
	public function isPropagationStopped():bool;
}