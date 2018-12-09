<?php

namespace wfw\daemons\rts\server\websocket;

/**
 * Permet d'associer des listeners à des event et de dispatcher un event
 */
interface IWebsocketEventObserver {
	/**
	 * @param null|string ...$events Liste des événements dont on souhaite obtenir les listeners
	 * @return IWebsocketListener[][]
	 */
	public function getListeners(?string... $events):array;
	/**
	 * @param string             $event Nom de l'événement à écouter
	 * @param IWebsocketListener $listener Ecouteur
	 * @return mixed
	 */
	public function addEventListener(string $event,IWebsocketListener $listener);

	/**
	 * Si $listener est null, tous les listeners de $event sont supprimés.
	 * Si $event est null, $listener sera supprimé pour tous les événements.
	 * Si $listener et $event sont null, tous les listeners sont supprimés.
	 * @param IWebsocketListener $listener Nom de l'événement à écouter
	 * @param null|string        $event Ecouteur
	 * @return mixed
	 */
	public function removeEventListener(?IWebsocketListener $listener=null,?string $event=null);
}