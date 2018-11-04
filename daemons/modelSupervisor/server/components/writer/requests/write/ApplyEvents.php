<?php
namespace wfw\daemons\modelSupervisor\server\components\writer\requests\write;

use wfw\engine\core\domain\events\EventList;

/**
 * Applique la liste d'événements sur les models chargés par le MSServer.
 */
final class ApplyEvents implements IWriterWriteRequest {
	/** @var EventList $_events */
	private $_events;
	/** @var string $_sessionId */
	private $_sessionId;

	/**
	 * ApplyEvents constructor.
	 *
	 * @param string    $sessId Identifiant de session
	 * @param string    $events Liste des événements à appliquer, sérialisée.
	 */
	public function __construct(string $sessId,string $events) {
		$this->_events = $events;
		$this->_sessionId = $sessId;
	}

	/**
	 * @return string EventList sérialisée.
	 */
	public function getEvents():string{
		return $this->_events;
	}

	/**
	 * @return null|string Identifiant de session
	 */
	public function getSessionId(): ?string {
		return $this->_sessionId;
	}

	/**
	 * @return mixed Données du message.
	 */
	public function getData() {
		return $this->_events;
	}

	/**
	 * @return mixed Paramètres du message
	 */
	public function getParams() {
		return $this;
	}

	/**
	 * @return array
	 */
	public function __sleep() {
		return [
			"_sessionId"
		];
	}
}