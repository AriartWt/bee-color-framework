<?php

namespace wfw\daemons\rts\server\websocket;

/**
 * Dispatcher d'événement websocket
 */
interface IWebsocketEventDispatcher {
	/**
	 * @param IWebsocketEvent[] $events Evenements à dispatcher
	 */
	public function dispatch(IWebsocketEvent... $events):void;
}