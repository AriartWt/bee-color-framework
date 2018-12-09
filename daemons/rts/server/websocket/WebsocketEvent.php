<?php

namespace wfw\daemons\rts\server\websocket;

/**
 * Evenement websocket
 * Evenement de base :
 *      connected : une socket est connectée (handhaske Ok),
 *      closed : une socket est fermée,
 *      accepted : une connection a été acceptée (no handshake yet),
 *      rejected : une connection a été rejectée (handshake Ko),
 *      msg_recieved : un message a été reçu,
 *      msg_sent : un message a été envoyé
 */
final class WebsocketEvent implements IWebsocketEvent{
	/** @var string $_name */
	private $_name;
	/** @var array $_data */
	private $_data;

	/**
	 * WebsocketEvent constructor.
	 *
	 * @param string $name Nom de l'événement
	 * @param array  $data Données de l'événement
	 */
	public function __construct(string $name,array $data) {
		$this->_name = $name;
		$this->_data = $data;
	}

	/**
	 * @return string Nom de l'événement
	 */
	public function getName(): string {
		return $this->_name;
	}

	/**
	 * @return array Données associées à l'event
	 */
	public function getData(): array {
		return $this->_data;
	}
}