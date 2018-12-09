<?php

namespace wfw\daemons\rts\server\websocket;

/**
 * Evenement d'un websocket
 */
interface IWebsocketEvent {
	/**
	 * @return string Nom de l'événement
	 */
	public function getName():string;

	/**
	 * @return array Données associées à l'event
	 */
	public function getData():array;
}