<?php

namespace wfw\daemons\rts\server\websocket;

/**
 * Protocole permettant de gérer les écritures et les lectures dans des websockets.
 */
interface IWebsocketProtocol extends IWebsocketEventDispatcher {
	/**
	 * Accepte une nouvelle connexion
	 * @param resource $socket Socket network
	 */
	public function accept($socket):void;

	/**
	 * Vérifie toutes les sockets et dispatch les événements en fonction des lectures/écritures dans
	 * les socket clients
	 */
	public function process():void;
}