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
	 * Accepte une nouvelle connexion et crée un utilisateur
	 *
	 * @param resource $socket Socket network
	 * @return IWebsocketUser
	 */
	public function accept($socket): IWebsocketUser {
		// TODO: Implement accept() method.
	}

	/**
	 * @param IWebsocketUser $user Utilisateur dont on doit lire les données
	 * @return null|string Null si le message est incomplet. Le message sinon.
	 */
	public function readUserMessage(IWebsocketUser $user): ?string {
		// TODO: Implement readUserMessage() method.
	}

	/**
	 * Envoie les données en attente destinées à l'utilisateur
	 *
	 * @param IWebsocketUser $user Utilisateur ayant des données à envoyer.
	 * @return int|null Nombre de bytes écrits. Null si rien à écrire.
	 */
	public function writeUserMessages(IWebsocketUser $user): ?int {
		// TODO: Implement writeUserMessages() method.
	}
}