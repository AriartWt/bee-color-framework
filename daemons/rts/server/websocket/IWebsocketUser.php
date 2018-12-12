<?php

namespace wfw\daemons\rts\server\websocket;

/**
 * Utilisateur connecté via une websocket.
 */
interface IWebsocketUser {
	/**
	 * @return string Identifiant de la websocket
	 */
	public function id():string;

	/**
	 * @return resource socket client
	 */
	public function socket();

	/**
	 * @param null|string $sessId Si précisé, change l'identifiant de la session de l'utilisateur
	 * @return null|string identifiant de la session PHP associée.
	 */
	public function sessionId(?string $sessId=null):?string;

	/**
	 * @param bool|null $state Si précisé, change l'état de handshaked
	 * @return bool
	 */
	public function handshaked(?bool $state=null):bool;

	/**
	 * @param null|string $str Si précisé, ajoute $str au buffer
	 * @return string buffer
	 */
	public function readBuffer(?string $str):string;

	/**
	 * @param bool|null $state Si précisé, change l'état de handlingPartialPacket
	 * @return bool
	 */
	public function handlingPartialPacket(?bool $state=null):bool;

	/**
	 * @param string $framedMessage Message à ajouter à la queue.
	 */
	public function addToWriteQueue(string $framedMessage):void;

	/**
	 * @return null|string Null si queue vide, message restant à envoyer sinon
	 */
	public function peekWriteQueue():?string;

	/**
	 * Consomme $length caractères sur le message courant dans la queue. (First In, First Out)
	 * Si le message est entièrement consommé, l'entrée de la queue est supprimée.
	 * Si aucun autre message n'est disponible, la queue reste vide
	 * @param int $length Nombre de caractères à consommer
	 * @return string Données consommées
	 */
	public function consumeWriteQueue(int $length):string;
}