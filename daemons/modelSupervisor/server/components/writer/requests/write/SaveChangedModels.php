<?php
namespace wfw\daemons\modelSupervisor\server\components\writer\requests\write;

/**
 * Permet de déclencher une sauvegarde synchrone des models impactés par les modifications
 * effectuées depuis la dernière sauvegarde.
 * La sauvegarde est effectuée de manière synchrone.
 * Si une autre sauvegarde est en cours, les serveur attend qu'elle soit terminée avant de procéder à celle-ci.
 * Aucune autre requête ne sera traitée par le WriterComponentWorker pendant ce temps.
 */
final class SaveChangedModels implements IWriterWriteRequest {
	/** @var string $_sessionId */
	private $_sessionId;

	/**
	 * SaveChangedModels constructor.
	 *
	 * @param string $sessId Identifiant de session
	 */
	public function __construct(string $sessId) {
		$this->_sessionId = $sessId;
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
		return null;
	}

	/**
	 * @return mixed Paramètres du message
	 */
	public function getParams() {
		return null;
	}
}