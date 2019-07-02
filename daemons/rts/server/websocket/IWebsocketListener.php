<?php

namespace wfw\daemons\rts\server\websocket;

/**
 * Listener de IWebsocketEvent
 */
interface IWebsocketListener {
	/**
	 * @param IWebsocketEvent $event Evenement
	 */
	public function applyWebsocketEvent(IWebsocketEvent $event):void;
}