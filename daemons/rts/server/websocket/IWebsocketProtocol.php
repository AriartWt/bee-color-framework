<?php

namespace wfw\daemons\rts\server\websocket;

/**
 * Protocole permettant de gérer les écritures et les lectures dans des websockets.
 */
interface IWebsocketProtocol extends IWebsocketEventDispatcher {
	/**
	 * Accepte une nouvelle connexion et crée un utilisateur
	 *
	 * @param resource $socket Socket network
	 * @return IWebsocketUser
	 */
	public function accept($socket):IWebsocketUser;

	/**
	 * @param IWebsocketUser $user Utilisateur dont on doit lire les données
	 * @return null|string Null si le message est incomplet. Le message sinon.
	 */
	public function readUserMessage(IWebsocketUser $user):?string;

	/**
	 * Envoie les données en attente destinées à l'utilisateur
	 *
	 * @param IWebsocketUser $user Utilisateur ayant des données à envoyer.
	 * @return int|null Nombre de bytes écrits. Null si rien à écrire.
	 */
	public function writeUserMessages(IWebsocketUser $user):?int;
}