<?php
namespace wfw\daemons\modelSupervisor\server\components\writer\requests\admin;

/**
 * Ordonne la reconstruction de tous les models gérés par le writer
 *
 * ATTENTION : Cette opération est effectuée de manière synchrone et peut être lente en
 * fonction du nombre de models, de leurs algorythme d'application des événements,
 * de leurs indexes et du nombre d'événements à réappliquer.
 */
final class RebuildAllModels implements IWriterAdminRequest {
	/** @var string $_sessId */
	private $_sessId;

	/**
	 * RebuildAllModels constructor.
	 *
	 * @param string $sessId Identifiant de session de l'utilisateur
	 */
	public function __construct(string $sessId) {
		$this->_sessId = $sessId;
	}

	/**
	 * @return null|string Identifiant de session
	 */
	public function getSessionId(): ?string {
		return $this->_sessId;
	}

	/**
	 * @return mixed Données du message.
	 */
	public function getData() {
		return null;
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
			"_sessId"
		];
	}
}